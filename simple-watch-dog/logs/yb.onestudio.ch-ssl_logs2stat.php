<?php
/**
 * Auxiliary to $0.sh bash script
 *
 * Extract information from cPanel logs.
 * Unfortunately user_name is not available...
 */

define('CLI_MODE', true);
define('WDstats', '`yb-watch-dog`.`wd_visitor_stats`');
define('SQL', '/tmp/tempo.sql');

require_once '/Users/yb/Sites/adb/wp-config.php';
require_once '/Users/yb/Sites/adb/wp-includes/class-wpdb.php';
require_once '/Users/yb/Sites/adb/wp-includes/class-wp-hook.php';

define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
define('wddb', new wpdb(DB_USER, DB_PASSWORD, 'yb-watch-dog', DB_HOST));
//print_r(wddb);

$log_file = $argv[1];
if (!file_exists($log_file)) die("Can't get input file $log_file\n");

$sql = [];
foreach(explode("\n",file_get_contents($log_file)) as $line) {
//176.126.133.217 - - [31/Jul/2026:10:07:45 -0400] "GET /restor/device/ HTTP/2" 200 14596 "https://yb.onestudio.ch/restor/" "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36"
echo "\n";
    echo "$line\n";
    
    // Combined Log Format:
    // %h %l %u %t "%r" %>s %b "%{Referer}i" "%{User-agent}i"
    //$pattern = '/^(\S+) \S+ \S+ \[([^\]]+)\] "[^"]*" \d+ \S+ "\[^"]*" "([^"]*)"/';
    
    //            ---------        ----      -------           -------       --------
    //$pattern = '/([0-9\.]*) - - \[(.*)\]\s\"([^\"]*)\"[^\"]*\"([^\"]*)\"\s\"([^\"]*)\"/';
    $pattern = '/([0-9\.]*) - - \[(.*)\] \"([^\"]*)\" [^\"]*\"([^\"]*)\" \"([^\"]*)\"/';

    if (!preg_match($pattern, $line, $match))  continue;
    
    //print_r($match);
    $R = ['mode'      => 'test',	  
	  'time'      => getParisTime($match[2]),
	  'remote'    => $match[1],
	  'user_id'   => 999,
	  'user_name' => 'nameless',
	  'user_agent'=> $match[5],
	  'duration'  => '0',
	  'uri'       => explode(' ', $match[3])[1],
    ];
    // print_r($R);
    if ($result = wddb->get_results($s="SELECT * FROM ".WDstats." WHERE remote='$R[remote]' AND uri='$R[uri]' AND time='$R[time]' AND user_agent='$R[user_agent]'")) {
	echo "$s\n";
	foreach($result as $r) {
	    //print_r($r);
	}
    } else {
	$insert = wddb->get_results($s='INSERT INTO '.WDstats.' ('.join(',',array_keys($R)).') VALUES ("'.join('","',array_values($R)).'");');
	echo "$s\n";
    }
//    if (@$ccccc++) break;
    echo "-----\n";
}

/**
 * $logTime = "31/Jul/2026:12:08:17 -0400";
 */
function getParisTime($logTime) {
    // Parse the timestamp and set the target time zone
    echo __function__."($logTime)\n";
    $date = DateTime::createFromFormat('d/M/Y:H:i:s O', $logTime);
    $date->setTimezone(new DateTimeZone('Europe/Paris'));
  //$reply = $date->format('d/M/Y:H:i:s O');
    $reply = $date->format('Y-m-d H:i:s');
    echo __function__."($logTime) $reply\n";
    return $reply;
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

