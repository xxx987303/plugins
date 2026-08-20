<?php
// Prevent direct access
if (!defined('CLI_MODE')) define('CLI_MODE', empty($_SERVER['HTTP_HOST']));
//if (!defined('ABSPATH') && !CLI_MODE) exit;

/**
 */
function WD_message($textP='', $level='debug') {
    return function_exists('YB_message') ? YB_message($textP, $level) : false;
}

/**
 * Fix WP "feature" when it blindly adds quotes to SQL
 * Change "`database.table`" -> "`database`.`table`"
 */
function YB_query_fix( $sql0 ) {
    $sql = preg_replace_callback('/((UPDATE|INTO|EXISTS|FROM|JOIN)\s([a-zA-Z0-9_\-\`]+\.[a-zA-Z0-9_\-\`]+))/',
				 function ($matches) {
				     list($db,$table) = explode('.', str_replace('`','',$matches[3]));
				     return "$matches[2] `$db`.`$table`"; },
				 $sql0);
    if(0) if($sql0 != $sql){
	echo "<span style=font-size:small>";
	//echo "was: $sql0<br>";
	//echo "now: $sql<br>";
	echo "</span>";
    }
    return $sql;
}
if (!CLI_MODE) add_filter( 'query', 'YB_query_fix' );

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
 * Populate 'wd_visitor_stats' table
 */
function WD_track_visitor() {
    global $wpdb;
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
            $user_id = null;
	}
	
	if (empty($_POST['duration'])) $_POST['duration'] = -1;
	$wpdb->insert(WDstats, ($a=['user_id'   => (($u=$user_id) ? $u : 0),
 				    'user_name' => (($u=$user_id) ? $name : 'anonymous'),
 				    'uri'       => $_SERVER['REQUEST_URI'],
 				    'user_agent'=> ($ua=$_SERVER['HTTP_USER_AGENT']),
 				    'remote'    => $_SERVER['REMOTE_ADDR'],
 				    'duration'  => $_POST['duration'],
 				    'mode'      => (PRODUCTION_MODE ? 'prod' : 'debug'),
 				    'time'      => current_time('mysql')]));
	WD_log($rec = join(', ', [$a['user_name'], WD_getOS($ua), WD_getBrowser($ua), $a['uri']]), $user_id);
	echo "\n<!-- ".__function__." writes $rec -->\n";
    }
}
if (!CLI_MODE) add_action('wp_head', 'wd_track_visitor');

/**
 */
function WD_create_tables() {
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    $charset_collate = wddb->get_charset_collate();
//  wddb->get_results("USE ".WDDB);
    wddb->get_results("CREATE TABLE IF NOT EXISTS ".WDstats." (
      id mediumint(9) DEFAULT 0,
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
      time datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
      remote   varchar(32),
      country  varchar(32)
    ) $charset_collate;");

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

    foreach (wddb->get_results("SELECT CONCAT('user_id=',user_id,' remote=',remote) AS x, user_id FROM ".WDstats." GROUP BY x") as $r) {
        $cache = [];
        foreach(wddb->get_results(sprintf("SELECT id, time,UNIX_TIMESTAMP(time) AS ts, duration FROM %s WHERE user_id=%s ORDER BY ts DESC LIMIT 9999",
                                          WDstats,
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
            $q = sprintf("UPDATE %s SET duration=$duration WHERE id=%d", WDstats, $v['id']);
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
/**
 * MacOS  - Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.5 Safari/605.1.15
 * iPhone OS 17.5.1 - Mozilla/5.0 (iPhone; CPU iPhone OS 17_5_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) FxiOS/127.0...
 * iPad OS 17.5.1   - Mozilla/5.0 (iPad; CPU iPad OS 17_5_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.5.1 Mobile/...
 * iPad OS 17.5.1   - Mozilla/5.0 (iPad; CPU iPad OS 17_5_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.5.1 ...
*/
function WD_getOS($user_agent) {
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
    return $browser;
}

function WD_getBrowserVersion($browser, $user_agent) {
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

    return $version;
}

/**
 * Get the country name from IP, save the result DB using the first 3 digits of IP as key 
 */
function WD_getCountry($remote){
    global $wpdb;

    $remote = str_replace('`', '', $remote);
    if (in_array($remote, LOCALHOSTs)) return 'localhost';
    
    // Extract only the first 3 numbers from the IP address
    $comp = explode('.',$remote);
    $remote3 = implode('.',[$comp[0],$comp[1],$comp[2]]);

    // Get the country from database, if exists
    $sql = "SELECT * FROM " . WDremotes . " WHERE remote = '$remote3'";
    foreach(wddb->get_results($sql) as $r) {
	$country = $r->country;
	WD_message("$sql: $remote - $country", 'debug');
    }
    if (!empty($country)) return $country;
    
    // Update database if a new entry found
    $country = (@json_decode(file_get_contents("http://ip-api.com/json/".$remote)))->country;
    $wpdb->insert(WDremotes, ['remote'   => $remote3,	                                        
			     'country'  => $country]);
    WD_message("Updating DB: $remote --> $country", 'warn');
    return $country;
};

function joinX($a, $skipEmpty=true){
    if (is_array($a)) {
	$r = "";
	foreach($a as $k=>$v) {
	    if (is_array($v)) { $r .= joinX($v, $skipEmpty); continue; } 
	    if (true && empty($v) && $v !== 0 && $v !== '0') continue;
	    if (empty($v)||$k=='comment') continue;
	    $r .= "$k=>$v ";
	}
	return '['.trim($r).']';
    }else{
	return var_export($a,true);
    }
}

