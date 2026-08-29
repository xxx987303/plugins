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

$MY_SITES  = '/(restor|adb)/[a-z0-9]*/';
define('CLI_MODE', true);
define('WDdaemon',  '`yb-watch-dog`.`wd_daemon`');
define('WDdaemon2', '`yb-watch-dog`.`wd_daemon_nonfiltered`');
define('WDremotes', '`yb-watch-dog`.`wd_remotes`');
define('WDvisits',  '`yb-watch-dog`.`wd_visits`');
define('SQL', '/tmp/tempo.sql');
define( 'WPINC', 'wp-includes' );

require_once '/Users/yb/Sites/adb/wp-config.php';
require_once '/Users/yb/Sites/adb/wp-includes/class-wpdb.php';
require_once '/Users/yb/Sites/adb/wp-includes/class-wp-hook.php';
require_once '/Users/yb/Sites/adb/wp-includes/class-wp-user.php';
require_once '/Users/yb/Sites/adb/wp-includes/cache.php';
require_once __dir__ . '/../includes/functions.php';

define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
define('wddb', new wpdb(DB_USER, DB_PASSWORD, 'yb-watch-dog', DB_HOST));
define('wpdb', new wpdb(DB_USER, DB_PASSWORD, DB_NAME, DB_HOST));
$wp_object_cache  = new WP_Object_Cache();
$wpdb = wddb;

if (empty($argv[1])) $argv[1] = '';
if (($log_file = $argv[1]) && file_exists($log_file)) {
    populate_WDdaemon($log_file);
    populate_WDdaemon_nonfiltered($log_file);
} elseif (preg_match('/update/i', $argv[1])) {
    WDaemon_to_WDvisits();
} else {
    die("What do you want me to to?\n");
}
exit;

/**
 * ABS(TIMESTAMPDIFF(SECOND,datetime1,$datetime2))
 */
function WDaemon_to_WDvisits() {
    global $MY_SITES;
    
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
	    $user_id = '';
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
		  'user_id'   => (($id=$user_id) ? $id : 0),
		  'user_name' => user_name($user_id),
		  'uri'       => $d->d_uri,
		  'remote'    => $d->d_remote,
		  'duration'  => $duration,
		  'mode'      => 'daemon',
		  'user_agent'=> $d->d_user_agent,
	    ];
	    $sql = "INSERT INTO ".WDvisits." (".join(',',array_keys($R)).") VALUES ('".join("','",array_values($R))."')";
	    //wddb->get_results($sql);
	    echo preg_replace(["{INSERT.*VALUES }","{daemon.*}"], ["INSERT",""],$sql)."\n";
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

    foreach(explode("\n",file_get_contents($log_file)) as $line) {
	if (empty(trim($line))) continue;
	
	// Parse the log
	// 84.17.46.88 - - [14/Oct/2025:12:25:25 -0400] "GET /restor/bt/ HTTP/2" 404 13715 "https://yb.onestudio.ch/restor/" "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)...
	$pattern = '/([0-9\.]*) - - \[(.*)\] \"([^\"]*)\" [^\"]*\"([^\"]*)\" \"([^\"]*)\"/';             // recommended
	$pattern = '/([0-9\.]*) - - \[(.*)\] \"([^\"]*)\" ([\S]*) ([\S]*)[^\"]*\"([^\"]*)\" \"([^\"]*)\"/'; // my extended
	//           ----IP----     --date--   -"uri"-    --code- --code-        ---url--      ---UA--

	// Combined Log Format:
	// %h %l %u %t "%r" %s %b "%{Referer}i" "%{User-agent}i"

	if (!preg_match($pattern, $line, $match))  die("??? no match for line \"$line\"\n");
      //if (!preg_match($pattern, $line, $match))  continue;
	if (count(array_values($match)) != 8) { print_r($match); die("????\n"); }
	//print_r($match);	if (@$cont++ > 5) exit;

	$uri   = explode('/',explode(' ', $match[3])[1]);
	if (empty($uri[2])) continue;
	$d_uri = "/$uri[1]/".explode('?',$uri[2])[0]."/";

	// Accept "my sites" only
	if (!preg_match("{".$MY_SITES."}", $d_uri)) continue;
	    // Accept code 200 only
	    //if ($match[4] != 200) continue;

	if (empty($ParisTime = getParisTime($match[2]))) {
	    die("Empty Paris time\n");
	}
	$R = ['d_time'      => $ParisTime,
	      'd_remote'    => $match[1],
	      'd_uri'       => $d_uri,
	    //'d_user_agent'=> $match[5],
	      'd_user_agent'=> $match[7],
	];

	//echo "\n$line\n";
	if ($res=wddb->get_results("SELECT * FROM ".WDdaemon." WHERE d_remote='$R[d_remote]'  AND d_time='$R[d_time]' AND d_uri='$R[d_uri]' AND d_user_agent='$R[d_user_agent]'")) {
	    if (($c=count($res)) !=1 ) die("Count = $c???\n");
	    unset($res[0]->d_user_agent);
	    printf("FOUND %s\n",joinX($res[0]));
        } else {
	    $sql = 'INSERT INTO '.WDdaemon.' ('.join(',',array_keys($R)).') VALUES ("'.join('","',array_values($R)).'");';
	    wddb->get_results($sql);
	    echo "$sql\n";
	    // if (@$ccccc++) break;
	}
    }
}

/**
 * Read cPanel log file and fill fill wd_daemon database table.
 * Typical entry in cPanel logs:
 * 176.126.133.217 - - [31/Jul/2026:10:07:45 -0400] "GET /restor/device/ HTTP/2" 200 14596 "https://yb.onestudio.ch/restor/" "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) ...
 */
function populate_WDdaemon_nonfiltered($log_file){
    
    foreach(explode("\n",file_get_contents($log_file)) as $line) {
	if (empty(trim($line))) continue;
	
	$pattern = '/([0-9\.]*) - - \[(.*)\] \"([^\"]*)\" ([\S]*) ([\S]*)[^\"]*\"([^\"]*)\" \"([^\"]*)\"/'; // my extended
	//           ----IP----     --date--   -"uri"-    --code- --code-        ---url--      ---UA--
	
	if (!preg_match($pattern, $line, $match))  die("??? no match for line \"$line\"\n");
	if (count(array_values($match)) != 8) { print_r($match); die("????\n"); }

	$uri = explode(' ', $match[3])[1];
	echo "uri=$uri\n";

	if (empty($ParisTime = getParisTime($match[2]))) die("Empty Paris time\n");
	
	$R = ['d_time'      => $ParisTime,
	      'd_remote'    => $match[1],
	      'd_uri'       => $uri,
	      'd_user_agent'=> $match[7],
	];

	//echo "\n$line\n";
	if ($res=wddb->get_results("SELECT * FROM ".WDdaemon2." WHERE d_remote='$R[d_remote]'  AND d_time='$R[d_time]' AND d_uri='$R[d_uri]' AND d_user_agent='$R[d_user_agent]'")) {
	    if (($c=count($res)) !=1 ) die("Count = $c???\n");
	    unset($res[0]->d_user_agent);
	    printf("FOUND nonfiltered %s\n",joinX($res[0]));
        } else {
	    $sql = 'INSERT INTO '.WDdaemon2.' ('.join(',',array_keys($R)).') VALUES ("'.join('","',array_values($R)).'");';
	    wddb->get_results($sql);
	    echo "$sql\n";
	}
    }
}

/**
 * $logTime = "31/Jul/2026:12:08:17 -0400";
 */
/*
function getParisTime($logTime) {
    // Parse the timestamp and set the target time zone
    $date = DateTime::createFromFormat('d/M/Y:H:i:s O', $logTime);
    $date->setTimezone(new DateTimeZone('Europe/Paris'));
  //$reply = $date->format('d/M/Y:H:i:s O');
    $reply = $date->format('Y-m-d H:i:s');
    //echo __function__."($logTime) $reply\n";
    return $reply;
}
 */
function getParisTime($logTime) {
    $date = DateTime::createFromFormat('d/M/Y:H:i:s O', $logTime);
    if ($date === false) {
        return null; // caller can skip/flag this line instead of crashing
    }
    $date->setTimezone(new DateTimeZone('Europe/Paris'));
    return $date->format('Y-m-d H:i:s');
}

function user_name($user_id) {
    if (empty($user_id)) $user_id = 0;
    if ($u = wpdb->get_results($sql="SELECT * FROM `".DB_NAME."`.`wp_users` WHERE ID = $user_id")) {
	return $u[0]->display_name;
    } else {
	echo "sql=$sql\n";
	return 'anonymous';
    }
}

function is_multisite() { return false; }

    function get_user_by( $field, $value ) {
	$userdata = WP_User::get_data_by( $field, $value );
	if ( ! $userdata ) {
		return false;
	}
	$user = new WP_User();
	$user->init( $userdata );

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

