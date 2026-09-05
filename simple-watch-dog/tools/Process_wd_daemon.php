<?php
/**
 *                                                        YB, Summer 2026
 *
 * Log files processor(s).
 * The log files from "yb.onstudio.ch" exist in 2 incarnations:
 * - All the Apache logs are saved automatically by cPanel.
 * - DIY plugin "watch dog" (WD), currently WordPress oriented, tracks users and sends
 *       information to yb_watch_dog.wd_visits table on the fly whenever the user opens a pge.
 *
 * The WD data contain the user logins data, which cPanel does not support.
 * But, since WD soft was written after the start of the site, some entries áre missing there.
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

define('DRY_RUN',  false);
define('CLI_MODE', true);
define('WDdaemon',  '`yb_watch_dog`.`wd_daemon`');
define('WDremotes', '`yb_watch_dog`.`wd_remotes`');
define('WDvisits',  '`yb_watch_dog`.`wd_visits`');
define('SQL', '/tmp/tempo.sql');
define( 'WPINC', 'wp-includes' );

$root = __dir__ . '/../../../../';
$MY_SITES  = '/(restor|adb)/([a-z0-9]+|55120-2|from-archive)/';
if (DRY_RUN) for($k=0; $k<5; $k++) echo "----------------------------------- DRY_RUN \n";

require_once "$root/Sites/adb/wp-config.php";
require_once "$root/Sites/adb/wp-includes/class-wpdb.php";
require_once "$root/Sites/adb/wp-includes/class-wp-hook.php";
require_once "$root/Sites/adb/wp-includes/class-wp-user.php";
require_once "$root/Sites/adb/wp-includes/cache.php";
require_once "$root/Sites/adb/wp-includes/formatting.php";
require_once __dir__ . "/../includes/functions.php";

define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
define('wddb', new wpdb(DB_USER, DB_PASSWORD, 'yb_watch_dog', DB_HOST));
define('wpdb', new wpdb(DB_USER, DB_PASSWORD, DB_NAME, DB_HOST));
$wp_object_cache  = new WP_Object_Cache();
$wpdb = wddb;

if (@$argv[1] == 'remote') {
    //
    // Initialise wd_remotes from visits
    // ===============================
    //
    WD_init_remotes();
} elseif (in_array(@$argv[1],['visits','import','daemon'])) {
    //
    // Populate wd_visits/wd_daemom from cPanel logs
    // =================================
    //
    if (0) {
	foreach(['May','Jun','Jul','Aug','Sep'] as $m) {
	    WD_import_cPanel("yb.onestudio.ch-ssl_log-$m-2026", $argv[1]);
	}
    } else {
	    WD_import_cPanel("yb.onestudio.ch-ssl_log-???-20??", $argv[1]);
    }
}else{
    echo"
    visits - Import cPanel logs into wd_visits
    daemon - Import cPanel logs into wd_daemon
    remote - Init wd_remotes table
    ";
    die("\n   What do you want me to to?\n\n");
}
exit;

/**
 */
function WD_init_remotes() {

    WD_getCC('95.75.213.135',  !DRY_RUN);
    WD_getCC('176.223.173.229',!DRY_RUN);

    //if (!DRY_RUN) wddb->get_results("TRUNCATE TABLE ".WDremotes);
    foreach (wddb->get_results("SELECT remote FROM ".WDvisits." WHERE remote NOT IN ('".join("','",LOCALHOSTs)."') GROUP BY remote") as $r) {
 WD_getCC($r->remote, !DRY_RUN);
    }

    wddb->get_results("UPDATE wd_visits  SET   user_name='Антон' WHERE user_id=4");
}

/**
 *
 */
function WD_import_cPanel($logs_template, $mode) {
    global $MY_SITES;

    $count = 0;
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

    if(0){
	$fmt = "%4s  %-17s %-17s %-37s %5s\n";
	printf($fmt, 'ID', 'Country', 'Remout', 'Domain', 'Count');
	foreach(wddb->get_results("SELECT CONCAT_WS('|',user_id,remote) AS item, count(*) AS count ".
 				  "FROM WD_visits ".
 				  "WHERE user_id >0 AND remote NOT IN ('" . implode("','", LOCALHOSTs) ."') ".
 				  "GROUP BY item ".
 				  "ORDER BY user_id") as $r) {
	    list($user_id,$remote) = explode('|',$r->item);
	    printf($fmt, $user_id, WD_getCC($remote), $remote, getDomain($remote), $r->count);
	}
    }

    // Change the misplaced IPs
    WD_fix_SwissIPs();

    $updates   = ['84.17.46.88'    => '212.233.85.247',
                  '185.214.97.147' => '194.230.146.126',
                  '185.225.28.204' => '194.230.146.126'];
    //
    // Loop over the cPanel logs
    //
    $logs = shell_exec("ls -1 ".__dir__."/../logs/$logs_template");
    foreach($logs=explode("\n",$logs) as $k=>$log_file){
	echo "-$k------------------------------------ ".basename($log_file)."\n";
	if (empty($log_file)) continue;
	foreach(explode("\n",file_get_contents($log_file)) as $line) {
	    if (empty(trim($line))) continue;

	    $pattern = '/^(\S+) \S+ \S+ \[([^\]]+)\] "\S+ (\S+) [^"]*" \d+ \S+ "[^"]*" "([^"]*)"/';
	    if (!preg_match($pattern, $line, $match)) continue;
	    if (count(array_values($match)) != 5) { print_r($match); die("????\n"); }

	    $remote    = str_replace(array_keys($updates),array_values($updates),$match[1]);
	    $time      = getParisTime($match[2]); // e.g. 31/Jul/2026:12:08:17 -0400
	    $uri       = $match[3];
	    $ua        = $match[4] !== '' ? $match[4] : '(unknown)';

	    // Get nake uri
	    $uri = explode('?', $uri.'??', 2)[0];
	    if (!preg_match(";$MY_SITES;", $uri)) continue;
	    if ( preg_match("{wp-|https|/feed/}", $uri)) continue;
 	
	    $country   = @WD_getCC($remote, true);
	    //if ($country == 'United States') continue;
 	    $args = ($mode == 'daemon'
 		? ['d_time'    => $time,
		   'd_uri'     => $uri,
		   'd_remote'  => $remote,
		   'd_user_agent'=> $ua]
 		: ['time'      => $time,
                   'user_id'   => $user_id,
                   'user_name' => WD_display_name($user_id,'display_name'),
 		   'uri'       => $uri,
                   'user_agent'=> $ua,
 		   'remote'    => $remote,
                   'mode'      => 'daemon']);

	    if ($mode == 'daemon') {
 		$db = WDdaemon;
		$sql = "SELECT * FROM $db WHERE ";
		foreach($args as $k=>$v) $sql .= " $k='$v' AND";
 		$toWrite = !wddb->get_results($s=preg_replace("/ AND$/", '', $sql)); 
	    } else {
 		$db = WDvisits;
 		// Rely of info in wd_remotes table
 		$user_id = @$usersByIP[$remote3($remote)];
 		if ($remote == '95.75.213.135') $user_id = 2;
 		if (empty($user_id))            $user_id = 999;
 	
 		$MAX_DELAY = 60;
 		$sql = "SELECT *,ABS(TIMESTAMPDIFF(SECOND,time,'$time')) AS delay ".
 		       " FROM $db ".
 		       " WHERE uri='$uri' AND remote='$remote' AND user_agent='$ua'".
 		       " HAVING delay>=0 AND delay<$MAX_DELAY ".
 		       //" GROUP BY CONCAT_WS('|','$time',uri,remote,user_agent) ".
 		       " ORDER BY delay LIMIT 1";
 		$toWrite = !($r=wddb->get_results($sql));
 		// printf("OK     %s %3d %-25s %-15s %-16s %s\n", $time, $user_id, $uri, $remote, $country,  substr($ua, 0,55));
	    }

	    if ($toWrite) {
		$sql = "INSERT INTO $db (".join(',',array_keys($args)).") VALUES ('".join("','",array_values($args))."')";
 		WD_save_sql($sql,$mode);
 	
 		if (DRY_RUN){
 		    printf("INSERT %s %3d %-25s %-15s %-16s %s\n", $time, $user_id, $uri, $remote, $country,  substr($ua, 0,55));
 		} else {
 		    echo "$sql;\n";
 		    $count++;
 		    wddb->get_results($sql);
 		}
	    } else {
		// echo "OK $sql\n";
	    }
	}
	if ($mode == 'daemon') set_duration($log_file);
    }

    echo "\nWritten $count records\n\n";

    // Change the misplaced IPs
    WD_fix_SwissIPs();
}

/**
 * Created by Claude 2026-08-19
 *
 * Approximate session durations from an Apache access log (Combined Log Format)
 * by grouping requests by IP + User-Agent, then splitting into sessions
 * whenever the gap between two consecutive requests exceeds a timeout.
 *
 */
function set_duration($logfile) {
    global $MY_SITES;

    date_default_timezone_set("Europe/Paris");

    $timeoutSeconds = 15 * 60;
    $pattern = '/^(\S+) \S+ \S+ \[([^\]]+)\] "\S+ (\S+) [^"]*" \d+ \S+ "[^"]*" "([^"]*)"/';

    if (!($handle = fopen(__dir__."/../logs/".basename($logfile), 'r'))) {
	fwrite(STDERR, "Failed to open $logfile.\n");
	exit(1);
    }

    $requests = []; // key = ip|user-agent, value = array of unix timestamps
    while (($line = fgets($handle)) !== false) {
	if (!preg_match($pattern, $line, $m)) continue; // skip lines that don't match (malformed / different log format)
	$ip        = $m[1];
	$timeStr   = $m[2]; // e.g. 31/Jul/2026:12:08:17 -0400
	$uri       = $m[3];
	$userAgent = $m[4] !== '' ? $m[4] : '(unknown)';
	if (!preg_match(";$MY_SITES;", $uri)) continue;

	// Parse Apache log time format: d/M/Y:H:i:s O
	if (!($dt = DateTime::createFromFormat('d/M/Y:H:i:s O', $timeStr))) continue; // skip unparseable timestamps
	//printf("%-15s %s %-15s\n", $ip, $timeStr, $uri);
	$key = $ip . '|' . $userAgent;
	//$requests[$key][] = getParisTime($timeStr);
	$requests[$key][] = $dt->getTimestamp();
    }
    fclose($handle);

    // Output CSV header
    //echo "ip,user_agent,session_start,session_end,duration_seconds,request_count\n";

    foreach ($requests as $key => $timestamps) {
	[$ip, $userAgent] = explode('|', $key, 2);
	sort($timestamps);

	$sessionStart = $timestamps[0];
	$prevTime     = $timestamps[0];
	$requestCount = 1;

	$flushSession = function ($start, $end, $count) use ($ip, $userAgent) {
            $duration = $end - $start;
            $startFmt = date('Y-m-d H:i:s', $start);
            $endFmt   = date('Y-m-d H:i:s', $end);
            $uaEscaped = str_replace('"', '""', $userAgent);
	    //        echo "\"$ip\",\"$uaEscaped\",\"$startFmt\",\"$endFmt\",$duration,$count\n";
	    $sql = "UPDATE wd_daemon SET d_duration = $duration WHERE d_time = '$startFmt'";
	    echo "$sql;\n";
	    if (!DRY_RUN) wddb->get_results($sql);
	};

	for ($i = 1; $i < count($timestamps); $i++) {
            $gap = $timestamps[$i] - $prevTime;
            if ($gap > $timeoutSeconds) {
 		// Gap too large: close out the current session, start a new one
 		$flushSession($sessionStart, $prevTime, $requestCount);
 		$sessionStart = $timestamps[$i];
 		$requestCount = 0;
            }
            $prevTime = $timestamps[$i];
            $requestCount++;
	}

	// Flush the final session for this ip+user-agent group
	$flushSession($sessionStart, $prevTime, $requestCount);
    }
}


/**
 * 2024-07-17 10:26:39 | 185.214.97.147  |                                  | 2         | Spain           |
 * 2024-07-17 10:50:43 | 185.225.28.204  |                                  | 2         | North Macedonia |
 * 2024-07-24 15:25:01 | 194.230.146.126 | mob-194-230-146-126.cgn.sunrise. | 2         | Switzerland     |
 * 2025-10-05 16:22:04 | 212.233.85.247  |                                  | 3         | Russia
 * 2025-10-14 18:24:34 | 84.17.46.88     | unn-84-17-46-88.cdn77.com        | 3         | The Netherlands |
 */
function WD_fix_SwissIPs(){

    $verbose = false;
    if ($verbose) echo "\n\n".__function__."())\n\n===================\n";

    $WDremotes = '`yb_watch_dog`.`wd_remotes`';
    $WDvisits  = '`yb_watch_dog`.`wd_visits`';
    $WDdaemon  = '`yb_watch_dog`.`wd_daemon`';
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
 	if ($verbose) {
 	    if ($r=wddb->get_results($sql)) { echo "Found  ".$r[0]->count." {$prefix}{$field} = '$from'\n"; $Fail++;}
 	    else                            { echo "OK, no  $tbl WHERE {$prefix}{$field} = '$from'\n"; }
 	}
     }
 }
 if (!$Fail) break;

 // Deleting fields
 foreach ($tblDel as $table=>$prefix) {
     foreach($updates as $from=>$to) {
 	$sql = "DELETE FROM $table WHERE {$prefix}{$field} = '$from'";
 	if ($verbose) echo "$sql\n";
 	wddb->get_results($sql);
     }
 }
 // Updating fields
 foreach ($tblRep as $table=>$prefix) {
     foreach($updates as $from=>$to) {
 	$sql = "UPDATE $table SET {$prefix}{$field} = '$to' WHERE {$prefix}{$field} = '$from'";
 	if ($verbose) echo "$sql\n";
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
 *
 */
function WD_save_sql($sql, $mode='import') {
    global $__finction___init;
    if ($mode == 'daemon') return;

    $fn = __dir__.'/Fixes.sql';

    // Just to be sure...
    $sql = trim($sql,';') . ";\n";

    if (!@$__finction___init++) file_put_contents($fn,'');
    file_put_contents($fn, $sql, FILE_APPEND);
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

