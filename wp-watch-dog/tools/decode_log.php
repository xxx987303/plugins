<?php
/**
 * 194.230.146.126 - - [24/Jul/2024:09:25:01 -0400] "GET /restor/wp-includes/js/dist/interactivity.min.js?ver=6.5.4 HTTP/1.1" 200 12814 "https://yb.onestudio.ch/restor/" "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36"
 *
 */

define('CLI_MODE', true);
define('ABSPATH',__dir__);
if (!function_exists('YB_message')) {
    function YB_message($text, $mode) {}
}

include_once __dir__ . '/../includes/functions.php';


// Print help
if (!isset($argv[1])) $argv[1] = 'log';
if       (strpos($argv[1], '-h') !== false) {
    exit;
} elseif (strpos($argv[1], 'log') !== false) {
    $format = 'short';
} else {
    $format = sql;
}

foreach(preg_split("/\n/",shell_exec('ls -1 cpanel_log*')) as $file) {
    if (!file_exists($file)) continue;
    echo "$file\n";
    foreach (preg_split("/\n/", file_get_contents($file)) as $line) {
        if (false && !preg_match('/194.230.146.126/', $line)){
            continue;
        } elseif (preg_match("/doing_wp_cron/", $line)) {
            continue;
        } elseif (preg_match('/^([0-9\.]+) (.*) \[(\S+) [^"]+"(GET|POST) ([^ ]*)[^"]*[^"]"[^"]*"[^"]*"[^"]"([^"]*)"/', $line, $matches)) {
            $ip   = $matches[1];
            $time = $matches[3];
            $time = UTCTimeToLocalTime($matches[3], 'Europe/Stockholm', 'd/M/Y:H:i:s');
            $uri  = $matches[5];
            $user_agent = $matches[6];
            //echo "$user_agent\n";
            //echo "remote=\"$ip\" time=\"$time\" uri=\"$uri\" user_agent=\"$user_agent\"\n";
            if ($format == 'short') {
                printf ("%9s %15s %-15s %-10s %-17s %-15s\n",
                        $time, $ip, wd_getCC($ip), wd_getBrowser($user_agent), wd_getOS($user_agent), $uri);                
            } else {
                //echo "remote=\"$ip\" time=\"$time\" uri=\"$uri\" user_agent=\"$user_agent\"\n";
                printf ("%9s, %15s, %10s\n",$time, $ip, $user_agent);
            }
            //echo "XXXXXXXXXXXXXXXX '$matches[1]' '$matches[3]' '$matches[5]' '$matches[6]'\n";
        } elseif (preg_match("/^([0-9\.]+) (.*) \[(\S+) [^\"]+\"(GET|POST) ([^ ]*) [^\"]*\"([^\"]*)\"/", $line, $matches)) {
            echo "YYYYYYYYYYYYYYYY '$matches[1]' '$matches[3]' '$matches[5]' '$matches[6]'\n";
            //    XXXXXXXXXXXXXXXX 185.176.246.72 17/Jul/2024:04:09:41 /restor/wp-includes/js/dist/interactivity.min.js?ver=6.5.4 ' 200 12814 ' 
        } else {
            echo "??? $line\n";
        }
    }
}


/**
 * 24/Jul/2024:09:25:01
 */
function Z_UTCTimeToLocalTime($time, $tz = '', $FromDateFormat = 'Y-m-d H:i:s', $ToDateFormat = 'Y-m-d H:i:s')   {
    if ($tz == '') $tz = date_default_timezone_get();
    $utc_datetime = DateTime::createFromFormat($FromDateFormat, $time, new DateTimeZone('UTC'));
    
    $local_datetime = $utc_datetime;
    
    $local_datetime->setTimeZone(new DateTimeZone($tz));
    return $local_datetime->format($ToDateFormat);
}
