<?php

// Set CLI_MODE
if (!defined('CLI_MODE')) define('CLI_MODE', empty($_SERVER['HTTP_HOST']));

// Localhost might look different...
define('LOCALHOSTs', ['127.0.0.1', '::1', 'localhost']);

if (function_exists('wp_enqueue_style')) wp_enqueue_style ('charts-css', get_stylesheet_directory_uri() . '/photoswipe/photoswipe.css');

/**
 * Called from YB_end_output_buffering
 * Insert WD_messages into the page html code
 */
function YB_show_messages($content){
    if (SHOW_MESSAGES) {
	$messages = "\n\n<!-- Start ".__function__." -->\n" . WD_message('print') . "\n\n<!-- End  ".__function__." -->\n";
	$content = str_replace("</main>", "$messages\n</main>\n", $content);
    }
    return $content;
}

/**
 * Safe against add_filter add_action
 */
function WD_message($text='', $color='black', $truncate=true) {
    global $WD_messages,  $indent, $prev;
    static $r;

    if ($ee = in_array($text,['entry','exit'])) $color='magenta';
    if (CLI_MODE) {
	if (!$ee) echo WD_getCaller(2)."(): $text\n";
	return;
    }

    $st_ind  = '/tmp/indent';
    $handler = '/tmp/WD_message.txt';
    if (!isset($indent)) { $indent = 0; file_put_contents($st_ind, $indent); }
    $indent = file_get_contents($st_ind);

    // Open the handler
    if (empty($WD_messages)) file_put_contents($handler, "<div class='yb-comments'><h3>Messages...</h3>\n<code style='font-size:x-small'>\n");
    @$WD_messages++;

    // Handle 'print'
    if ($text == 'print') {
	// Close the handler
	file_put_contents($handler, "</code>\n</div>\n", FILE_APPEND);
	return file_get_contents($handler);
    }

    // Handle 'exit'
    if ($text == 'exit') $indent--;

    // Strange... But Claude thinks this is predictable
    if ($indent < 0) {
	error_log("WD_message: indent went negative=$indent! pid=" . getmypid() . " req=" . ($_SERVER['REQUEST_URI'] ?? 'cli'));
	$indent = 0; error_log("WD_message: indent is reset to $indent");
        error_log(print_r(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5), true));
    }

    // Add the message to the handler
    $r = [' array ( ' => '[',
	  ' ) '       => ']',
	  ' => '      => '=>'];
    if (false){
	file_put_contents($handler,
 			  str_repeat('&nbsp;',2*max(0,$indent)).($ee ? "<span style=font-weight:bold>".WD_getCaller(2)."():</span> ":"").
 			  "<span style=color:$color>".truncatePreserveWord(str_replace(array_keys($r), array_values($r), joinX($text)), 120)."</span>".($ee?"":" ($indent)")."<br>\n",
 			  FILE_APPEND);
    }else{
	if ($text != $prev && !$ee) file_put_contents($handler,
 						      str_repeat('&nbsp;',2*max(0,$indent))."<span style=font-weight:bold>".WD_getCaller(2)."():</span> ".
 						      "<span style=color:$color>".truncatePreserveWord(str_replace(array_keys($r), array_values($r), joinX($text)), 130, $truncate).
 						      "</span><br>\n",
 						      FILE_APPEND);
	$prev = $text;
    }
    // Handle 'entry'
    if ($text == 'entry') { $indent++; }
    // Save indent
    file_put_contents($st_ind, $indent);
}

/**
 * Fix the WP "feature" when it blindly adds quotes to SQL
 * Change "`database.table`" -> "`database`.`table`"
 */
function YB_query_fix( $sql0 ) {
    $sql = preg_replace_callback('/((UPDATE|INTO|EXISTS|FROM|JOIN)\s([a-zA-Z0-9_\-\`]+\.[a-zA-Z0-9_\-\`]+))/',
				 function ($matches) {
                            list($db,$table) = explode('.', str_replace('`','',$matches[3]));
                            return "$matches[2] `$db`.`$table`"; },
				 $sql0);
    if($sql0 != $sql){
	// WD_message('entry'); WD_message($sql,'blue',$truncate=true);  WD_message('exit');
    }
    return $sql;
}
if (!CLI_MODE) add_filter( 'query', 'YB_query_fix' );

/**
 */
function WD_display_name($user_id,$f='display_name') {
    if     ($user_id == 999) return ($f == 'display_name' ? 'x999' : 999);
    elseif (empty($user_id)) $user_id = 0;
    if ($u = wpdb->get_results($sql="SELECT * FROM `".DB_NAME."`.`wp_users` WHERE ID = $user_id")) {
	return $u[0]->{$f};
    } else {
	echo __function__."($user_id,$f): sql=$sql\n";
	return 'anonymous';
    }
}

/**
 */
function WD_user_not_monitored($r) {
    if     (isset($r->user_login)) {$arg1 = 'login'; $arg2 = $r->user_login; }
    elseif (isset($r->r_user_id))  {$arg1 = 'id';    $arg2 = $r->r_user_id; }
    elseif (isset($r->user_id))    {$arg1 = 'id';    $arg2 = $r->user_id; }
    else   die("Please add argument " . joinX($r)."\n"); 
    $not_monitored = PRODUCTION_MODE && WD_SKIP_ADMIN && ($arg2 == 1 || $arg2=='yb');
    WD_message("Not monitored ".$arg2." - ".var_export($not_monitored,true));
    return $not_monitored;
}

/**
 */
function WD_add_items($admin_bar) {
//    if ( ! current_user_can( 'manage_options' ) ) { return; }
    // This is where the magic works.
    $admin_bar->add_menu([ 'id'    => 'wd_top_button',
 		   'parent'=> null,
 		   'group' => null,
 		   'title' => '<span class="ab-icon"></span>Статистика',
 		   'href'  => '/'.WD_HOME.'/stat/',
 		   'meta'  => ['title' => 'Статистика', 'class' => '' ]]);
    echo '<style>#wpadminbar #wp-admin-bar-wd_top_button .ab-icon:before {content: "\f239"; color: #FF9800; top: 3px;}</style>';
}
if (!CLI_MODE) add_action('admin_bar_menu', 'WD_add_items',  40);

/**
 */
function hide_admin_bar_toggle() {
    echo '<style>.show-admin-bar { display: none !important; }</style>';
}
if (!CLI_MODE) add_action('admin_head-profile.php',   'hide_admin_bar_toggle');
if (!CLI_MODE) add_action('admin_head-user-edit.php', 'hide_admin_bar_toggle');

/**
 * Populate 'wd_visits' table
 */
function WD_track_visitor() {
    global $wpdb;
    if (CLI_MODE) return;
    
    $user_id = 0;
    echo "\n<!-- ".__function__." fires -->\n";
    
    if (preg_match('/(github|wp-content)/', $_SERVER['REQUEST_URI'])) {
	// WD_log(__function__."(): IGNORE $_SERVER[REQUEST_URI]");
    }else{
	if (is_user_logged_in()) {
            WD_create_tables();
            WD_set_durations();
            $current_user = wp_get_current_user();
            $user_id  = $current_user->ID;
            $name     = $current_user->display_name;
	} else {
            $user_id = 0;
	}
	if (empty($_POST['duration'])) $_POST['duration'] = -1;
	$wpdb->insert(WDvisits, ($args=['user_id'   => (($u=$user_id) ? $u : 0),
  					'user_name' => (($u=$user_id) ? $name : 'anonymous'),
  					'uri'       => $_SERVER['REQUEST_URI'],
  					'user_agent'=> ($ua=$_SERVER['HTTP_USER_AGENT']),
  					'remote'    => $_SERVER['REMOTE_ADDR'],
  					'duration'  => $_POST['duration'],
  					'mode'      => (PRODUCTION_MODE ? 'prod' : 'debug'),
  					'time'      => current_time('mysql')]));
	WD_log($rec = join(', ', [$args['user_name'], WD_getOS($ua), WD_getBrowser($ua), $args['uri']]), $user_id);
	echo "\n<!-- ".__function__." writes $rec -->\n";
    }
}
if (!CLI_MODE) add_action('wp_head', 'wd_track_visitor');

/**
 */
function WD_create_tables() {
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    $charset_collate = wddb->get_charset_collate();
    wddb->get_results("CREATE TABLE IF NOT EXISTS ".WDvisits." (
      id varchar(32), default NULL,
      time datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
      remote varchar(32),
      user_id bigint(20) UNSIGNED DEFAULT NULL,
      user_name  varchar(255),
      user_agent varchar(255),
      duration int DEFAULT 0,
      uri  varchar(255),
      mode  varchar(16),
      UNIQUE KEY `log_entry` (`time`,`user_id`)
    ) $charset_collate;");
    wddb->get_results("CREATE TABLE IF NOT EXISTS " . WDremotes . " (
      r_time datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
      r_remote   varchar(32) DEFAULT NULL,
      r_domain   varchar(32) DEFAULT NULL,
      r_user_id  varchar(16) DEFAULT NULL,
      r_country  varchar(32) DEFAULT NULL
    ) $charset_collate;");
    wddb->get_results("CREATE TABLE IF NOT EXISTS ".WDdaemon." (
      d_time datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
      d_uri  varchar(255),
      d_remote varchar(32),
      d_duration int DEFAULT NULL,
      d_user_agent varchar(255)
    ) $charset_collate;");
/*
    wddb->get_results("CREATE TABLE IF NOT EXISTS ".WDdaemon2." (
      d_time datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
      d_uri  varchar(255),
      d_remote varchar(32),
      d_user_agent varchar(255)
    ) $charset_collate;");
*/
}
if (!CLI_MODE) register_activation_hook(__FILE__, 'WD_create_tables');

/**
 */
function UTCTimeToLocalTime($time, $tz = '', $FromDateFormat = 'Y-m-d H:i:s', $ToDateFormat = 'Y-m-d H:i:s')   {
    if ($tz == '') $tz = date_default_timezone_get();
    $utc_datetime = DateTime::createFromFormat($FromDateFormat, $time, new DateTimeZone('UTC'));

    $local_datetime = $utc_datetime;

    $local_datetime->setTimeZone(new DateTimeZone($tz));
    return $local_datetime->format($ToDateFormat);
}

/**
 * array(3) {
 *   [0]=> object(stdClass)#1470 (2) { ["user_id"]=> string(1) "1" ["visits"]=> string(2) "42"}
 *   [1]=> object(stdClass)#1472 (2) { ["user_id"]=> string(1) "3" ["visits"]=> string(1) "4" }
 *   [2]=> object(stdClass)#1475 (2) { ["user_id"]=> string(1) "4" ["visits"]=> string(1) "6" }
 *      }
 */
function WD_set_durations() {

    echo "\n<!-- ".__function__." -->\n";

    foreach (wddb->get_results("SELECT CONCAT('user_id=',user_id,' remote=',remote) AS x, user_id FROM ".WDvisits." GROUP BY x") as $r) {
        $cache = [];
        foreach(wddb->get_results(sprintf("SELECT id, time,UNIX_TIMESTAMP(time) AS ts, duration FROM %s WHERE user_id=%s ORDER BY ts DESC LIMIT 9999",
                                          WDvisits,
                                          $r->user_id)) as $r1) {
            //WD_message(var_export($r,true));
            $cache[] = ['id'  => $r1->id,
                        'ts'  => $r1->ts,
                        'time'=> $r1->time,
                        'duration' => $r1->duration];
        }

        $updates = 0;
        foreach($cache as $k=>$v) {
            if ($v['duration'] >= 0) continue;
            if ($k == 0) {
                if ((date('U') - $v['ts']) < WD_TIMEOUT) continue;
                $duration = 0;
            } else {
                $duration = ($d=$cache[$k-1]['ts']-$v['ts']) > WD_TIMEOUT ? 0 : $d;
            }
            $q = sprintf("UPDATE %s SET duration=$duration WHERE id=%d", WDvisits, $v['id']);
            $updates++;
            wddb->get_results($q);
        }
        // WD_message($r->x . " $updates updates", 'warn');
    }
}

/**
 */
function WD_log($text='', $user_id=null) {
    $mode = (PRODUCTION_MODE ? 'prod' : 'debug');
//    if (!PRODUCTION_MODE || !$user_id) {
        $date = (true
                 ? UTCTimeToLocalTime(date('Y-m-d H:i:s'), 'Europe/Stockholm')
                 : date('Y-m-d H:i:s'));
        $text = "$date: $mode, $text";
        file_put_contents('/tmp/log', preg_replace('/\s+/', ' ', str_replace("\n", " ", $text))."\n", FILE_APPEND);
//    }
}

/**
 */
function WD_getCaller($level) {
    if (0) $dbt=debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,$level+1);
    else   $dbt=debug_backtrace(0,$level+1);
    //print_r($dbt);
    return isset($dbt[$level]['function']) ? $dbt[$level]['function'] : '?';
}

/**
/**
 * MacOS  - Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.5 Safari/605.1.15
 * iPhone OS 17.5.1 - Mozilla/5.0 (iPhone; CPU iPhone OS 17_5_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) FxiOS/127.0...
 * iPad OS 17.5.1   - Mozilla/5.0 (iPad; CPU iPad OS 17_5_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.5.1 Mobile/...
 * iPad OS 17.5.1   - Mozilla/5.0 (iPad; CPU iPad OS 17_5_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.5.1 ...
*/
function WD_getOS($user_agent) {
    WD_message('entry');
    $os_array = [
      '/\b(iphone[ _OS0-9]+)\b/i' => 'iPhone',
      '/\b(ipad[ _OS0-9]+)\b/i'   => 'iPad',
      '/android/i'            => 'Android',
      '/windows nt 12/i'      => 'Windows 12',
      '/windows nt 11/i'      => 'Windows 11',
      '/windows nt 10/i'      => 'Windows 10',
      '/windows nt 6.3/i'     => 'Windows 8.1',
      '/windows nt 6.2/i'     => 'Windows 8',
      '/windows nt 6.1/i'     => 'Windows 7',
      '/windows nt 6.0/i'     => 'Windows Vista',
      '/windows nt 5.2/i'     => 'Windows Server 2003/XP x64',
      '/windows nt 5.1/i'     => 'Windows XP',
      '/windows xp/i'         => 'Windows XP',
      '/windows nt 5.0/i'     => 'Windows 2000',
      '/windows me/i'         => 'Windows ME',
      '/win98/i'              => 'Windows 98',
      '/win95/i'              => 'Windows 95',
      '/win16/i'              => 'Windows 3.11',
      '/Mac ?OS|Lynx/i'       => 'MacOS',
      '/macintosh|mac ?os/i'  => 'MacOS',
      '/mac_powerpc/i'        => 'MacOS 9',
      '/linux/i'              => 'Linux',
      '/ubuntu/i'             => 'Linux',
      '/ipod/i'               => 'iPod',
      '/ipad/i'               => 'iPad',
      '/blackberry/i'         => 'BlackBerry',
      '/webos/i'              => 'Mobile',
      '/windows phone/i'      => 'Windows Phone'
  ];
  $os_platform = "Unknown";
  foreach ($os_array as $regex => $value) {
      if (preg_match($regex, $user_agent, $matches)) {
          if (in_array(($os_platform = $value), ['iPhone','iPad'])) {
              $os_platform = str_replace('_','.',$matches[1]);
          }
          break;
      }
  }
    WD_message("$os_platform",'warn');
    if ($os_platform == "Unknown") WD_message("$os_platform - $user_agent",'warn');
    WD_message('exit');
    return $os_platform;
}

/**
 * F-iPad- Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.0 Safari/605.1.15
 *-F..fox- Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:128.0) Gecko/20100101 Firefox/128.0
 *-Yandex- Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 YaBrowser/24.1.0.0 Safari/537.36
 *-Opera - Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36 OPR/111.0.0.0
 *-Safari- Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.5 Safari/605.1.15
 *-Bing  - Mozilla/5.0 (iPad; CPU iPad OS 17_5_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.5.1 Mobile/15E148 Safari/605.1.15 BingSapphire/1.0.420703001
 * Brave - Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36
 * Chrome- Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36
 * Vivaldi Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36
 * Lynx/2.8.9rel.1 libwww-FM/2.14 SSL-MM/1.4.1 OpenSSL/3.3.1
 */
function WD_getBrowser($user_agent, $count=0) {
    WD_message('entry');
    $browser_array = array(
        '/WordPress/'             => 'WordPress',
        '/Lynx/'                  => 'Lynx',
        '/(msie|trident)/i'       => 'Internet Explorer',
        '/(bing)/i'               => 'Bing',
        '/(firefox)/i'            => 'Firefox',
        '/(YaBrowser)/i'          => 'Yandex',
        ';( Ddg/);'               => 'DuckDuckGo',
        ';( OPR/);'               => 'Opera',
        '/(opera)/i'              => 'Opera',
        '/^(?!.*\bchrome\b).*safari.*$/i' => 'Safari',
        ';( edg/|edge);i'         => 'Edge',
        '/(chrome)/i'             => 'Chrome',
        '/(netscape)/i'           => 'Netscape',
        '/(maxthon)/i'            => 'Maxthon',
        '/(konqueror)/i'          => 'Konqueror',
        '/(mobile)/i'             => 'Handheld Browser',
    );

    $browser = "Unknown";
    foreach ($browser_array as $regex => $value) {
        if (preg_match($regex, $user_agent, $match)) {
            $browser = $value;
            break;
        }
    }
    if ($browser == "Unknown")   WD_message("$browser - $user_agent", 'warn');
    WD_message('exit');
    return $browser;
}

function WD_getBrowserVersion($browser, $user_agent) {
    WD_message('entry');
    $version = "Unknown Version";

    $version_array = array(
        'Internet Explorer' => '/msie\s([0-9.]+)/i',
        'Firefox' => '/firefox\/([0-9.]+)/i',
        'Safari' => '/version\/([0-9.]+)\s+safari/i',
        'Chrome' => '/chrome\/([0-9.]+)/i',
        'Edge' => '/edge\/([0-9.]+)/i',
        'Opera' => '/opera\/([0-9.]+)|opr\/([0-9.]+)/i',
    );

    if (array_key_exists($browser, $version_array) && preg_match($version_array[$browser], $user_agent, $matches)) {
        $version = $matches[1];
    }

    WD_message('exit');
    return $version;
}

/**
 * Get IP domain
 */
function getDomain($remote) {
    global $buffer;
    if (empty($reply = @$buffer[$remote])) {
	$domain = trim(shell_exec("host $remote"));;
	$reply = (preg_match('{not found}',$domain) ? '' : trim(preg_replace(['{.*pointer }','{\s}'], '', $domain)," .\\n\r\t\v\x00"));
	if (preg_match('{noserverscouldbereached}', $reply)) $reply = "From VPN?";
	$buffer[$remote] = $reply;
    }
    return $reply;
};

/**
 * Get the country name from IP, save the result DB using the first 3 digits of IP as key
 */
function WD_getCC($remote, $saveToDB=true) {
    global $buffer;
    if (in_array($remote, LOCALHOSTs)) return 'localhost';

    $DRY_RUN = defined('DRY_RUN') && DRY_RUN;
    
    WD_message('entry');
    if ($res=wddb->get_results("SELECT * FROM ".WDremotes." WHERE r_remote = '$remote' LIMIT 1")) {
	$country = $res[0]->r_country;
	if (empty($res[0]->r_domain) && !empty($d=getDomain($remote))) wddb->get_results("UPDATE ".WDremotes." SET r_domain='$d' WHERE r_remote = '$remote'");
    } elseif (!empty($buffer[$remote])){
	$country = $buffer[$remote];
    } else {
	$country = (@json_decode(file_get_contents("http://ip-api.com/json/".$remote)))->country;
	if ($saveToDB) {
	    // Get the "most frequent flyer"
	    if ($res=wddb->get_results("SELECT time,user_id  FROM ".WDvisits." WHERE remote = '$remote' ORDER BY time LIMIT 1")) { $time = $res[0]->time; $user_id = $res[0]->user_id; }

	    $args = [];
	    if (isset($time))    $args['r_time']    = $time;
	    if (isset($user_id)) $args['r_user_id'] = $user_id;
	    $args['r_remote']  = $remote;
	    $args['r_country'] = $country;
	    $args['r_domain']  = getDomain($remote);
	    WD_message("Adding:".joinX($args));
	    // Optionally save the query for later updates. DRY_RUN is debined only for CLI runs
	    $sql = "INSERT INTO ".WDremotes." (".join(',',array_keys($args)).") VALUES ('".join("','",array_values($args))."')";
	    if (defined('DRY_RUN')) WD_save_sql($sql); 
	    if (!$DRY_RUN) wddb->get_results("INSERT INTO ".WDremotes." (".join(',',array_keys($args)).") VALUES ('".join("','",array_values($args))."')"); 
	    
	    //Fix the wd_remots r_time. Вообще-то это выебон...
            foreach (wddb->get_results("SELECT r_remote,r_time,MIN(time) AS time_min ".
				       " FROM ".WDvisits.
				       " LEFT JOIN wd_remotes ON r_remote = remote") as $r) {
		if ($r->time_min != $r->r_time) {
		    $sql = "UPDATE ".WDremotes." SET r_time = '".$r->time_min."' WHERE r_remote = '".$r->r_remote."'";
		    if (defined('DRY_RUN')) WD_save_sql($sql); 
		    if (!$DRY_RUN) wddb->get_results($sql);
		    WD_message("Changing r_time($r->r_remote) $r->r_time  -->  $r->time_min");
		}
	    }
	} elseif (empty($buffer[$remote])) {
	    $buffer[$remote] = $country;
	}
    }
    WD_message('exit');
    return $country;
}

/**
 * After many changes it became a sort of "var_dump"
 */
function joinX($a, $skipEmpty=true){
    if (is_object($a)) $a = get_object_vars($a);
    if (is_array($a)) {
	$r = "";
	foreach($a as $k=>$v) {
	    if (is_array($v)) { $r .= joinX($v, $skipEmpty); continue; }
	    if (empty($v) && $v !== 0 && $v !== '0') continue;
	    if (empty($v)||$k=='comment') continue;
	    $r .= (is_string($v) ? "$k=>$v " : joinX($v));
	}
	return '['.str_replace(' => ','=>',preg_replace("/[\n\s]+/", " ", trim($r))).']';
    } elseif (is_int($a)) {
	return "$a";
    } elseif (is_string($a)) {
	return preg_replace("/[\n\s]+/", " ", trim($a));
    } else {
	var_dump($a);
	die("?????????\n");
    }
}

/**
 */
function truncatePreserveWord($string, $limit = 100, $toTruncate=true) {
    // Return the original string if it is already shorter than the limit
    return $string;
    if (!$toTruncate || mb_strlen($string) <= $limit) return $string;

    // Cut the string to the maximum allowed length
    $cutString = mb_substr($string, 0, $limit);

    // Find the last space within the cut string
    $lastSpace = mb_strrpos($cutString, ' ');

    // If a space exists, truncate up to that space; otherwise return the cut string
    if ($lastSpace !== false) return mb_substr($cutString, 0, $lastSpace) . '…';
    return $cutString . '…';
}
