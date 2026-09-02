<?php
/**
 *                                                        YB, Summer 2026
 *
 * Log files processor(s).
 * The log files from "yb.onstudio.ch" exist in 2 incarnations:
 * - All the Apache logs are saved automatically by cPanel.
 * - DIY plugin "watch dog" (WD), currently WordPress oriented, tracks users and sends
 *       information to yb-watch-dog.wd_visits table on the fly.
 *
 * The WD data contain the user logins data, which cPanel does not support.
 * But, since WD was created after the start of the site, some entries áre missing there.
 * 
 * The task of these scripts is to populate the WDvisits table with missing information,
 * gessing the user name from the IP address.
 *
 * Usage:
 *  - php Populate_wd_daemon.php logFileName
 *        extract from logFileName records with
 *        uri REGEXP (adb|restor)/[a-z0-9]*
 *  - php Populate_wd_daemon.php update
 *        adds missing records from wd_daemon to wd_visits,
 *        gessing the user_id
 */

$MY_SITES  = '/(restor|adb)/([a-z0-9]*/|XXXwp-login.php)';

define('CLI_MODE', true);
define('WDdaemon',  '`yb-watch-dog`.`wd_daemon`');
define('WDremotes', '`yb-watch-dog`.`wd_remotes`');
define('WDvisits',  '`yb-watch-dog`.`wd_visits`');
define('SQL', '/tmp/tempo.sql');
define( 'WPINC', 'wp-includes' );

require_once '/Users/yb/Sites/adb/wp-config.php';
require_once '/Users/yb/Sites/adb/wp-includes/class-wpdb.php';
require_once '/Users/yb/Sites/adb/wp-includes/class-wp-hook.php';
require_once '/Users/yb/Sites/adb/wp-includes/class-wp-user.php';
require_once '/Users/yb/Sites/adb/wp-includes/cache.php';
require_once '/Users/yb/Sites/adb/wp-includes/formatting.php';
require_once __dir__ . '/../includes/functions.php';

define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
define('wddb', new wpdb(DB_USER, DB_PASSWORD, 'yb-watch-dog', DB_HOST));
define('wpdb', new wpdb(DB_USER, DB_PASSWORD, DB_NAME, DB_HOST));
$wp_object_cache  = new WP_Object_Cache();
$wpdb = wddb;

if (empty($argv[1])) $argv[1] = '';
if (0 && preg_match('/7/', $argv[1])) {
    //
    // Impose user_id
    // ==============
    //
    WD_impose_user7();
}elseif (0 && ($ls=shell_exec("ls -1 $argv[1] 2>/dev/null"))) {
    //
    // Populate WDdaemon
    // =================
    //
    foreach(explode("\n",$ls) as $log_file) {
	populate_WDdaemon($log_file);
    }
} elseif (0 && preg_match('/update/i', $argv[1])) {
    //
    // Upgrade WDvisits with WDdaemon
    // ==============================
    //
    WDaemon_to_WDvisits();
} elseif ('remote' == $argv[1]) {
    //
    // Initialise wd_remotes from visits
    // ===============================
    //
    WD_init_remotes();
} elseif ('import' == $argv[1]) {
    //
    // Import wd_visits from cPanel logs
    // =================================
    //
    WD_import_cPanel();
} else {
    echo"
   import - Import cPanel logs
   remote - Init wd_remotes table 
";
    die("What do you want me to to?\n");
}
exit;

/**
 * ABS(TIMESTAMPDIFF(SECOND,datetime1,$datetime2))
 */
function WDaemon_to_WDvisits() {
    global $MY_SITES;
    
    echo "Doing ".__function__."\n";
    echo "Doing ".__function__."\n";
    echo "Doing ".__function__."\n";
    $MAX_DELAY = 60;
    
    // Collect WDdaemon file names
    $WDdaemon_fields = [];
    foreach(wddb->get_results("DESCRIBE ".WDdaemon) as $r) $WDdaemon_fields[] = $r->Field;
    //
    // Sanity check
    // ------------
    // All the records from WDvisits MUST present in WDdaemon, otherwise something is wrong...
    //
    $found = $mismatch = 0;
    $Not_in_WDdaemon = [];
    $delays = [$MAX_DELAY=>[]];
    foreach(wddb->get_results("SELECT * FROM ".WDvisits." WHERE uri REGEXP '$MY_SITES' AND remote NOT IN ('127.0.0.1','::1') ORDER BY time") as $v){
	if (empty($d = wddb->get_results($sql="SELECT *,ABS(TIMESTAMPDIFF(SECOND,d_time,'$v->time')) AS delay  FROM ".WDdaemon.
					      " WHERE d_uri='$v->uri' AND d_remote='$v->remote' AND d_user_agent='$v->user_agent' HAVING delay>=0 AND delay<$MAX_DELAY ORDER BY delay LIMIT 1"))) {
	    echo "CAN'T BE, NOT FOUND in WDdaemon: ".preg_replace('{d_user_agent.*HAVING}','HAVING',preg_replace("{.* WHERE}", "WHERE", $sql))."\n";
	    $mismatch++;
	    continue;
	}
	$delay = $d[0]->delay;
	//printf("delay=%3s TIMESTAMPDIFF(SECOND,10s d=%s v=%s\n", $delay, $d[0]->d_time, $v->time);
	if ($delay < $MAX_DELAY && $delay >= 0) {
	    $found++;
	    if (empty($delays[$delay])) $delays[$delay] = [];
	    //if (!in_array($v->remote,   $delays[$delay]))    $delays[$delay][] = $v->remote;
	    $delays[$delay][] = $v->remote;
	} else {
	    //if (!in_array($v->remote,$delays[$MAX_DELAY])) $delays[$MAX_DELAY][] = $v->remote;
	    $delays[$MAX_DELAY][] = $v->remote;
	    $Not_in_WDdaemon['Not_in_WDdaemon'][] = sprintf("Delay %9s %17s %17s %-25s %9s", (!empty($d) ? $d[0]->delay : '?'), $v->time, $v->remote, $v->uri, $v->user_name);
	    $mismatch++;
	}
    }
    ksort($delays);
    print_r($delays);
    print_r($Not_in_WDdaemon);
    if ($mismatch) echo("$mismatch mismatches, found $found\n");
    if ($mismatch) echo("$mismatch mismatches, found $found\n");
    if ($mismatch) echo("$mismatch mismatches, found $found\n");
    if ($mismatch) echo("$mismatch mismatches, found $found\n");
    if ($mismatch) echo("$mismatch mismatches, found $found\n");
    if ($mismatch) echo("$mismatch mismatches, found $found\n");
    if ($mismatch) echo("$mismatch mismatches, found $found\n");

    
    //
    // Do the work
    // -----------
    //
    $duration = -1;
    foreach (wddb->get_results("SELECT * FROM ".WDdaemon. " ORDER BY d_time") as $d) {
	
	$OneStudioDB = '`yb-watch-dog`.`wd_visits_onestudio`';
	if ($v = wddb->get_results($sql="SELECT *,TIMESTAMPDIFF(SECOND,'$d->d_time',time) AS delay FROM ".WDvisits.
					" WHERE '$d->d_uri'=uri AND '$d->d_remote'=remote AND '$d->d_user_agent'=user_agent HAVING delay >= 0 AND delay < $MAX_DELAY ORDER BY delay LIMIT 1")) {
	    // This session was registered by WDvisits, nothing to do,
	    // But check that the record is unique
	    //if (($c=count($v)) != 1) echo("$c records with timestamp ".joinX($v)."\n");
	    printf( "OK %s Delay %d secs %15s\n", $v[0]->time, $v[0]->delay, $v[0]->user_name);
	    $duration = $v[0]->delay;
	}else{
	    // Guess the user. First skip admin.
	    $user_id = 0;
	    foreach(wddb->get_results("SELECT user_id FROM $OneStudioDB WHERE user_id != '1' AND remote = '$d->d_remote' GROUP BY user_id") as $r){
		$user_id = $r->user_id;
		printf("%-16s user_id %s NON-ADMIN\n", $d->d_remote, $user_id);
	    }
	    //  try admin.
	    if (!$user_id) foreach(wddb->get_results("SELECT user_id FROM $OneStudioDB WHERE user_id = '1' AND remote = '$d->d_remote' GROUP BY user_id") as $r){
		$user_id = $r->user_id;
		printf("%-16s user_id %s ADMIN\n", $d->d_remote, $user_id);
	    }
	    
	    // This record is missing in WDvisits. Should be just one entry with this time
	    $R = ['time'      => $d->d_time,
		  'user_id'   => $user_id,
		  'user_name' => display_name($user_id),
		  'uri'       => $d->d_uri,
		  'remote'    => $d->d_remote,
		  'duration'  => $duration,
		  'mode'      => 'daemon',
		  'user_agent'=> $d->d_user_agent,
	    ];
	    $sql = "INSERT INTO ".WDvisits." (".join(',',array_keys($R)).") VALUES ('".join("','",array_values($R))."')";
	    wddb->get_results($sql);
	    echo preg_replace(["{INSERT.*VALUES }", "{daemon.*}"],
			      ["INSERT", ""],
			      str_replace(['(',')'],'',$sql))."\n";
	}
    }
}
    
/**
 * Read cPanel log file and fill fill wd_daemon database table.
 * Typical entry in cPanel logs:
 * 176.126.133.217 - - [31/Jul/2026:10:07:45 -0400] "GET /restor/device/ HTTP/2" 200 14596 "https://yb.onestudio.ch/restor/" "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) ...
 */
function populate_WDdaemon($log_file){
    global $MY_SITES;
    
    if (empty($log_file))  return;
    echo "Doing ".__function__."\n";
    echo "Doing ".__function__."\n";
    echo "Doing ".__function__."\n";
    
    foreach(explode("\n",file_get_contents($log_file)) as $line) {
	if (empty(trim($line))) continue;
	
	$pattern = '/([0-9\.]*) - - \[(.*)\] \"([^\"]*)\" ([\S]*) ([\S]*)[^\"]*\"([^\"]*)\" \"([^\"]*)\"/'; // my extended
	//           ----IP----     --date--   -"uri"-    --code- --code-        ---url--      ---UA--
	
	if (!preg_match($pattern, $line, $match))  {
	    echo ("??? no match for line \"$line\"\n");
	    continue;
	}
	if (count(array_values($match)) != 8) { print_r($match); die("????\n"); }

	// Get uri, skip rubbish
	$uri = explode('?', explode(' ', $match[3])[1].'??', 2)[0];
	//echo "preg_match(;$MY_SITES;, $uri)\n";
	if (!preg_match(";$MY_SITES;", $uri)) continue; // { echo "no match\n"; continue; }
	if ( preg_match("{css|/wp-(includes|cron)|/feed/|//}", $uri)) continue;
	
	// Look for login attempts
	if (preg_match('{/wp-login.php}', $uri)) {
	    // Consider only the known IPs
	    if (wddb->get_results($sql="SELECT r_remote FROM ".WDremotes." WHERE r_remote = '$match[1]' LIMIT 1")) {
		echo "ACCEPT KNOWN $match[1] $uri\n";
		//echo "$sql\n";
		//exit;
	    }else{
		echo "IGNORE $match[1] $uri\n";
		//echo "$sql\n";
		continue;
	    }
	}

	// IGNORE US, there are too many ....
	//if (WD_getCC($match[1],false) == 'United States') continue; 
	
	// Skip the crawler entries
	if (preg_match('{bot|crawler|spider|slurp|seek|checker|archiver|agent}',$match[7])) continue;

	// Convert server time to Europe TZ
	if (empty($ParisTime = getParisTime($match[2]))) die("Empty Paris time\n");
	
	$R = ['d_time'      => $ParisTime,
	      'd_remote'    => $match[1],
	      'd_uri'       => $uri,
	      'd_user_agent'=> $match[7],
	];

	//echo "\n$line\n";
	if ($res=wddb->get_results("SELECT * FROM ".WDdaemon." WHERE d_remote='$R[d_remote]'  AND d_time='$R[d_time]' AND d_uri='$R[d_uri]' AND d_user_agent='$R[d_user_agent]'")) {
	    if (($c=count($res)) !=1 ) die("Count = $c???\n");
	    unset($res[0]->d_user_agent);
	    //printf("FOUND nonfiltered %s\n",joinX($res[0]));
        } else {
	    $sql = 'INSERT INTO '.WDdaemon.' ('.join(',',array_keys($R)).') VALUES ("'.join('","',array_values($R)).'");';
	    wddb->get_results($sql);
	    echo "$sql\n";
	}
    }

    // Assign country to the IPs
    foreach(wddb->get_results("SELECT d_remote FROM ".WDdaemon." GROUP BY d_remote") as $r) {
	WD_getCC($r->d_remote);
    }
}

/**
 */
function WD_init_remotes() {

    define('dry_run', true);
    
    if (!dry_run) wddb->get_results("TRUNCATE TABLE ".WDremotes);
    foreach (wddb->get_results("SELECT remote FROM ".WDvisits." WHERE remote NOT IN ('".join("','",LOCALHOSTs)."') GROUP BY remote") as $r) {
	if (dry_run) {
	    echo "$r->remote\n";
	}else{
	    WD_getCC($r->remote);
	}
    }
    
    if (!dry_run) {
	wddb->get_results("UPDATE wd_remotes SET r_user_name='Антон' WHERE r_remote='93.158.130.173'");
	wddb->get_results("UPDATE wd_visits  SET   user_name='Антон' WHERE user_id=4");
    }
}

/**
 *
 */
function WD_import_cPanel($tp = 'yb.onestudio.ch-ssl_log-???-20??'){
    global $MY_SITES;

    $dry_run = true;
    
    echo "Doing ".__function__."\n";
    echo "Doing ".__function__."\n";
    echo "Doing ".__function__."\n";

    // Get RE
    $remote3 = function($remote){
    	$items = explode('.',$remote);
	$remoteRE = "$items[0].$items[1].$items[2].[0-9]+";
	return $remoteRE;
    };

    
    //
    // Build list of known countries
    //
    $usersByIP = [];
    // RE - Regular Expression
    foreach(wddb->get_results("SELECT r_remote,r_user_id,r_country FROM wd_remotes WHERE r_user_id>=0 GROUP BY r_remote") as $r){
	if ($r->r_country == 'Sweden') $r->r_user_id = 1;
	elseif ($r->r_user_id == 1) continue;
	$usersByIP[$remote3($r->r_remote)] = $r->r_user_id;
    }
    echo "\nIP regexps\n==========\n";
    asort($usersByIP);
    print_r($usersByIP);

    $fmt = "%4s  %-17s %-17s %-37s %5s\n";
    printf($fmt, 'ID', 'Country', 'Remout', 'Domain', 'Count');
    foreach(wddb->get_results("SELECT user_id, remote, count(*) AS count ".
			      "FROM WD_visits ".
			      "WHERE user_id IN (1,2,3,4,5,6,7,8) AND remote NOT IN ('" . implode("','", LOCALHOSTs) ."') ".
			      "GROUP BY CONCAT_WS(user_id,remote) ". // CONCAT_WS('|',uri,remote) ".
			      "ORDER BY user_id") as $r) {
	printf($fmt, $r->user_id, $r->remote, WD_getCC($r->remote), getDomain($r->remote), $r->count);
    }

    // Change the misplaced IPs
    WD_fix_SwissIPs();

    $updates   = ['84.17.46.88'    => '212.233.85.247',
                  '185.214.97.147' => '194.230.146.126',
                  '185.225.28.204' => '194.230.146.126'];

    // Loop over the cPanel logs
    $logs = shell_exec("ls -1 $tp");
    // print_r($logs);
    foreach($logs=explode("\n",$logs) as $k=>$log_file){
	echo "-$k------------------------------------ $log_file\n";
	if (empty($log_file)) continue;
	
	foreach(explode("\n",file_get_contents($log_file)) as $line) {
	    if (empty(trim($line))) continue;
	    $pattern = '/([0-9\.]*) - - \[(.*)\] \"([^\"]*)\" ([\S]*) ([\S]*)[^\"]*\"([^\"]*)\" \"([^\"]*)\"/'; // my extended
	    //           ----IP----     --date--   -"uri"-    --code- --code-        ---url--      ---UA--
	    
	    if (!preg_match($pattern, $line, $match)) continue;
	    if (count(array_values($match)) != 8) { print_r($match); die("????\n"); }
	    
	    // Get uri, skip rubbish
	    $uri = explode('?', explode(' ', $match[3])[1].'??', 2)[0];
	    if (!preg_match(";$MY_SITES;", $uri)) continue;

	    $remote = str_replace(array_keys($updates),array_values($updates),$match[1]);
	    //if (empty($usersByIP[$remote])) continue;
	  //$ok = preg_match('{(91.78.36.225|91.79.37.8)}', $remote, $m);
	    $ok = preg_match(($re='{^('.join('|',array_keys($usersByIP)).')$}'), $remote, $m);
	    if (!$ok) continue;
	    
	    $ua      = $match[7];
	    $time    = getParisTime($match[2]);
	    $country = WD_getCC($remote,false);

	    // Rely of info in wd_remotes table
	    $user_id = $usersByIP[$remote3($remote)];
	    //echo "XXX $remote $country $user_id\n";

	    $MAX_DELAY = 60;
	    $select = "SELECT *,ABS(TIMESTAMPDIFF(SECOND,time,'$time')) AS delay ".
		      " FROM ".WDvisits.
		      " WHERE uri='$uri' AND remote='$remote' AND user_agent='$ua'".
		      " HAVING delay>=0 AND delay<$MAX_DELAY ".
		      " ORDER BY delay LIMIT 1";
	    if ($r=wddb->get_results($select)) {
		// echo "Matched with delay=".$r[0]->delay."\n";
	    } else {
		$args=['time'      => $time,
                       'user_id'   => display_name($user_id, 'ID'),
                       'user_name' => display_name($user_id),
		       'uri'       => $uri,
                       'user_agent'=> $ua,
                       'remote'    => $remote,
                       'mode'      => 'daemon',
		       //'country'   => $country,
                       //'duration'  => $_POST['duration',,
		];
		if ($dry_run) {
		    echo "INSERT $time $user_id $remote $country $ua\n";
		}else{
	            $sql = "INSERT INTO ".WDvisits." (".join(',',array_keys($args)).") VALUES ('".join("','",array_values($args))."')";
		    echo "$sql\n";
		    wddb->get_results($sql);
		}
	    }
	}
    }
    // Change the misplaced IPs
    WD_fix_SwissIPs();
}

/**
 * 2024-07-17 10:26:39 | 185.214.97.147  |                                  | 2         | Spain           |
 * 2024-07-17 10:50:43 | 185.225.28.204  |                                  | 2         | North Macedonia |
 * 2024-07-24 15:25:01 | 194.230.146.126 | mob-194-230-146-126.cgn.sunrise. | 2         | Switzerland     |
 * 2025-10-05 16:22:04 | 212.233.85.247  |                                  | 3         | Russia
 * 2025-10-14 18:24:34 | 84.17.46.88     | unn-84-17-46-88.cdn77.com        | 3         | The Netherlands |
 */
function WD_fix_SwissIPs(){
    echo "\n\n".__function__."())\n\n===================\n";

    $WDremotes = '`yb-watch-dog`.`wd_remotes`';
    $WDvisits  = '`yb-watch-dog`.`wd_visits`';
    $WDdaemon  = '`yb-watch-dog`.`wd_daemon`';
    $tblRep    = [$WDvisits  => '',
		  $WDdaemon  => 'd_'];
    $tblDel    = [$WDremotes => 'r_'];
    $updates   = ['84.17.46.88'    => '212.233.85.247',
		  '185.214.97.147' => '194.230.146.126',
		  '185.225.28.204' => '194.230.146.126'];
    
    foreach(['remote'] as $field) {
	// Checking the result
	$Fail = 0;
	foreach (array_merge($tblRep,
			     $tblDel) as $tbl=>$prefix) {
	    foreach($updates as $from=>$to) {
		$sql = "SELECT {$prefix}{$field}, COUNT(*) AS count FROM $tbl WHERE {$prefix}{$field} = '$from' GROUP BY {$prefix}{$field}";
		if ($r=wddb->get_results($sql)) { echo "Found  ".$r[0]->count." {$prefix}{$field} = '$from'\n"; $Fail++;}
		else                            { echo "OK, no  $tbl WHERE {$prefix}{$field} = '$from'\n"; }
	    }
	}
	if (!$Fail) break;
	
	// Deleting fields
	foreach ($tblDel as $table=>$prefix) {
	    foreach($updates as $from=>$to) {
		$sql = "DELETE FROM $table WHERE {$prefix}{$field} = '$from'";
		echo "$sql\n";
		wddb->get_results($sql);
	    }
	}
	// Updating fields
	foreach ($tblRep as $table=>$prefix) {
	    foreach($updates as $from=>$to) {
		$sql = "UPDATE $table SET {$prefix}{$field} = '$to' WHERE {$prefix}{$field} = '$from'";
		echo "$sql\n";
		wddb->get_results($sql);
	    }
	}
	// Checking the result
	foreach (array_merge($tblRep,
			     $tblDel) as $tbl=>$prefix) {
	    foreach($updates as $from=>$to) {
		$sql = "SELECT * FROM $tbl WHERE {$prefix}{$field} = '$from'";
		if (wddb->get_results($sql)) { echo "??? still find  $tbl WHERE {$prefix}{$field} = '$from'\n"; }
		else                         { echo "OK, no  $tbl WHERE {$prefix}{$field} = '$from'\n"; }
	    }
	}
    }
    echo "\n";
}


/**
 * $logTime = "31/Jul/2026:12:08:17 -0400";
 */
function getParisTime($logTime) {
    $date = DateTime::createFromFormat('d/M/Y:H:i:s O', $logTime);
    if ($date === false) {
        return null; // caller can skip/flag this line instead of crashing
    }
    $date->setTimezone(new DateTimeZone('Europe/Paris'));
    return $date->format('Y-m-d H:i:s');
}

/**
 * Fix missing log records...
 */
/*
function WD_impose_user7(){
    // Set user_id
    foreach (wddb->get_results("SELECT remote FROM ".WDvisits." LEFT JOIN ".WDremotes." ON r_remote = remote WHERE r_country='Ukraine'") as $r) {
	wddb->get_results("UPDATE ".WDvisits. " SET   user_id = 7 WHERE   remote = '$r->remote'");
	wddb->get_results("UPDATE ".WDremotes." SET r_user_id = 7 WHERE r_remote = '$r->remote'");
    }
    // Check the result
    foreach([WDvisits=>'',WDremotes=>'r_'] as $db=>$prefix) {
	printf("\nTable %s\n",$db);
	//foreach(wddb->get_results("SELECT FROM $db {$prefix}remote,{$prefix}user_id WHERE {$prefix}user_id=7") as $r){
	foreach(wddb->get_results("SELECT * FROM $db WHERE {$prefix}user_id=7") as $r){
	    printf("%17s %s\n", $r->{$prefix}{remote}, $r->{$prefix}{user_id});
	}
    }
}
 */

/**
 */
function display_name($user_id,$field='display_name') {
    if (!empty($user_id) && ($u = wpdb->get_results($sql="SELECT * FROM `".DB_NAME."`.`wp_users` WHERE ID = $user_id"))) {
	return $u[0]->{$field};
    } else {
	//echo "sql=$sql\n";
	return ($field=='display_name' ? 'anonymous' : 0);
    }
}

function is_wp_error( $thing ) {
	$is_wp_error = ( $thing instanceof WP_Error );

	if ( $is_wp_error ) {
		/**
		 * Fires when `is_wp_error()` is called and its parameter is an instance of WP_Error.
		 *
		 * @since 5.6.0
		 *
		 * @param WP_Error $thing The error object passed to `is_wp_error()`.
		 */
		do_action( 'is_wp_error_instance', $thing );
	}

	return $is_wp_error;
}

function is_multisite() { return false; }

function get_user_by( $field, $value ) {
    $user = [];
    if ($field == 'id')    $field = 'ID';
    if ($field == 'login') $field = 'user_login';
    if ($u = wpdb->get_results($sql="SELECT * FROM `".DB_NAME."`.`wp_users` WHERE $field = '$value'")) $user = $u[0];
    //echo __function__."($field,$value) = ".joinX($user)."\n";
    return $user;
}

function YB_message($a='',$m='') {}

function wp_is_stream( $path ) {
	$scheme_separator = strpos( $path, '://' );
	if ( false === $scheme_separator ) {
		// $path isn't a stream.
		return false;
	}
	$stream = substr( $path, 0, $scheme_separator );
	return in_array( $stream, stream_get_wrappers(), true );
}

function wp_normalize_path( $path ): string {
	$path = (string) $path;

	static $cache = array();
	if ( isset( $cache[ $path ] ) ) {
		return $cache[ $path ];
	}

	$original_path = $path;
	$wrapper       = '';

	if ( wp_is_stream( $path ) ) {
		list( $wrapper, $path ) = explode( '://', $path, 2 );

		$wrapper .= '://';
	}

	// Standardize all paths to use '/'.
	$path = str_replace( '\\', '/', $path );

	// Replace multiple slashes down to a singular, allowing for network shares having two slashes.
	$path = (string) preg_replace( '|(?<=.)/+|', '/', $path );

	// Windows paths should uppercase the drive letter.
	if ( ':' === substr( $path, 1, 1 ) ) {
		$path = ucfirst( $path );
	}

	$cache[ $original_path ] = $wrapper . $path;
	return $cache[ $original_path ];
}


function wp_debug_backtrace_summary( $ignore_class = null, $skip_frames = 0, $pretty = true ) {
	static $truncate_paths;

	$trace       = debug_backtrace( false );
	$caller      = array();
	$check_class = ! is_null( $ignore_class );
	++$skip_frames; // Skip this function.

	if ( ! isset( $truncate_paths ) ) {
		$truncate_paths = array(
			wp_normalize_path( WP_CONTENT_DIR ),
			wp_normalize_path( ABSPATH ),
		);
	}

	foreach ( $trace as $call ) {
		if ( $skip_frames > 0 ) {
			--$skip_frames;
		} elseif ( isset( $call['class'] ) ) {
			if ( $check_class && $ignore_class === $call['class'] ) {
				continue; // Filter out calls.
			}

			$caller[] = "{$call['class']}{$call['type']}{$call['function']}";
		} else {
			if ( in_array( $call['function'], array( 'do_action', 'apply_filters', 'do_action_ref_array', 'apply_filters_ref_array' ), true ) ) {
				$caller[] = "{$call['function']}('{$call['args'][0]}')";
			} elseif ( in_array( $call['function'], array( 'include', 'include_once', 'require', 'require_once' ), true ) ) {
				$filename = $call['args'][0] ?? '';
				$caller[] = $call['function'] . "('" . str_replace( $truncate_paths, '', wp_normalize_path( $filename ) ) . "')";
			} else {
				$caller[] = $call['function'];
			}
		}
	}
	if ( $pretty ) {
		return implode( ', ', array_reverse( $caller ) );
	} else {
		return $caller;
	}
}

function _wp_filter_build_unique_id( $hook_name, $callback, $priority ) {
	if ( is_string( $callback ) ) {
		return $callback;
	}

	if ( is_object( $callback ) ) {
		// Closures are currently implemented as objects.
		$callback = array( $callback, '' );
	} else {
		$callback = (array) $callback;
	}

	if ( is_object( $callback[0] ) ) {
		// Object class calling.
		return spl_object_hash( $callback[0] ) . $callback[1];
	} elseif ( is_string( $callback[0] ) ) {
		// Static calling.
		return $callback[0] . '::' . $callback[1];
	}

	return null;
}

function has_filter( $hook_name, $callback = false, $priority = false ) {
    global $wp_filter;
    
    if ( ! isset( $wp_filter[ $hook_name ] ) ) {
	return false;
    }
    return $wp_filter[ $hook_name ]->has_filter( $hook_name, $callback, $priority );
}


function add_filter( $hook_name, $callback, $priority = 10, $accepted_args = 1 ) {
    global $wp_filter;
    
    if ( ! isset( $wp_filter[ $hook_name ] ) ) {
	$wp_filter[ $hook_name ] = new WP_Hook();
    }
    
    $wp_filter[ $hook_name ]->add_filter( $hook_name, $callback, $priority, $accepted_args );
    
    return true;
}

function apply_filters( $hook_name, $value, ...$args ) {
	global $wp_filter, $wp_filters, $wp_current_filter;

	if ( ! isset( $wp_filters[ $hook_name ] ) ) {
		$wp_filters[ $hook_name ] = 1;
	} else {
		++$wp_filters[ $hook_name ];
	}

	// Do 'all' actions first.
	if ( isset( $wp_filter['all'] ) ) {
		$wp_current_filter[] = $hook_name;

		$all_args = func_get_args(); // phpcs:ignore PHPCompatibility.FunctionUse.ArgumentFunctionsReportCurrentValue.NeedsInspection
		_wp_call_all_hook( $all_args );
	}

	if ( ! isset( $wp_filter[ $hook_name ] ) ) {
		if ( isset( $wp_filter['all'] ) ) {
			array_pop( $wp_current_filter );
		}

		return $value;
	}

	if ( ! isset( $wp_filter['all'] ) ) {
		$wp_current_filter[] = $hook_name;
	}

	// Pass the value to WP_Hook.
	array_unshift( $args, $value );

	$filtered = $wp_filter[ $hook_name ]->apply_filters( $value, $args );

	array_pop( $wp_current_filter );

	return $filtered;
}

