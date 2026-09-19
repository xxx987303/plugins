<?php
/**
 * Watchdog charts
 */

$notLocal = (PRODUCTION_MODE ? ' remote NOT IN ("' . implode('","', LOCALHOSTs) . '")' : ' 1');
//define('MY_SITE', " uri REGEXP '".WD_HOME."/[a-zA-Z0-9]+/' AND NOT REGEXP '/[\?]/' AND $notLocal AND user_agent IS NOT NULL");
define('MY_SITE', " uri REGEXP '".WD_HOME."/([a-zA-Z0-9]+|55120-2|from-archive)/' AND user_id>0 AND $notLocal AND user_agent IS NOT NULL");
define('VALID_URI', ['restor','restor_tmp','adb','adb_tmp']);

/*
 * Fire shortcodes
 */
add_shortcode( 'ChartBrowsers','WD_shortcode_ChartBrowsers');
add_shortcode( 'ChartUsers',   'WD_shortcode_ChartUsers');
add_shortcode( 'ChartTimes',   'WD_shortcode_ChartTimes');
add_shortcode( 'ChartPages',   'WD_shortcode_ChartPages');
add_shortcode( 'ChartCC',      'WD_shortcode_ChartCC');
add_shortcode( 'ChartOS',      'WD_shortcode_ChartOS');

/**
 *
 */
function YB_envoce_amchart($shortcode, $atts, $argsCodes) {
    global $dejaVu_amcharts, $chart_counter, $communicator;
    WD_message('entry');
    if (empty($atts)) WD_message("EMPTY ATTS shortcode=$shortcode argsCodes=".joinX($argsCodes));
    // Call JS
    $js = function($name) {
      //wp_enqueue_script(my_slug($name,'amcharts-'), YB_get_template_file_uri("js/amcharts_5_$name.js"), []);
	wp_enqueue_script(my_slug($name,'amcharts-'), plugin_dir_url(__FILE__) . "../js/amcharts_5_$name.js", []);
    };
	
    $x = $js('index') . $js('xy') . $js('percent') . $js("themes_Animated");
    $callingSequence = preg_replace(['/[()\',]/','/ array/','/ => /','/ \]/'],["","","=","]"],"[$shortcode ".var_export($atts,true)."]");

    if (empty($chart_counter[$shortcode])) { $chart_counter[$shortcode] = 0; }
    $chart_id = ++$chart_counter[$shortcode];
    $title = (empty($t=@$atts['title']) ? "Test imbedded $shortcode" : "atts[title]='$t'");
    if (isset($atts['id']))    unset($atts['id']);
    if (isset($atts['title'])) unset($atts['title']);
    
    $args = (empty($atts)
             ? WD_get_data($shortcode, $chart_id)
             : ['id'   => $chart_id,
                'title'=> $title,
                'data' => $atts]);
    WD_message("args = ".joinX($args));
    $args = repacker($argsCodes, $args);
    
  //$communicator[$shortcode][$ID] = $args;
    $communicator[$shortcode][$chart_id] = $args;
    if (empty($args['data'])) {
	$reply = current_user_can('manage_options') ? "<p>No statistics available yet for $callingSequence</p>" : '';
    } else {
        // Communicate arguments to JS 
	wp_enqueue_script('communicator', plugin_dir_url(__FILE__) . "../js/amcharts/communicator.js", ['jquery'], '1.0.0', true);
        wp_localize_script('communicator', "args", $communicator);

        // Load the executor
	wp_enqueue_script($shortcode, plugin_dir_url(__FILE__) . "../js/amcharts/$shortcode.js", ['jquery'], '1.0.0', true);
         
        $reply = "<div class='amchart_title'>".(empty($t=@$args['title'])?"":$t)."</div>\n"
             //. "<div class='chart_wrapper'>".(HIDE_CHART_TEST_DIV ? "" : "<p id='test$shortcode$ID'>$callingSequence</p>")
	       . "<div class='chart_wrapper'>".(1 ||  HIDE_CHART_TEST_DIV  ? "" : "<p id='test$shortcode$chart_id'>$callingSequence</p>")
             //. "<div id='chartdiv_$shortcode$ID' class='chartdiv'></div></div>\n";
               . "<div id='chartdiv_$shortcode$chart_id' class='chartdiv'></div></div>\n";
    }
    WD_message('exit');
    return $reply;
}

/**
 */
function WD_shortcode_ChartPages($atts, $content=null, $tag='' ) {
    WD_message('entry');
    $reply = YB_envoce_amchart('ChartPages',
                          $atts,
                          ['k' => 'page',
                           'v' => 'value']);
    WD_message('exit');
    return $reply;
}

/**
 */
function WD_shortcode_ChartTimes($atts, $content=null, $tag='' ) {
    WD_message('entry');
    $reply = YB_envoce_amchart('ChartTimes',
                          $atts,
                          ['k' => 'time',
                           'v' => 'value']);
    WD_message('exit');
    return $reply;
}

/**
 * [ ChartCC c1="Italy" v1=22, b2="Belgium"... ]
 */
function WD_shortcode_ChartCC($atts, $content=null, $tag='' ) {
    WD_message('entry');
    $reply = YB_envoce_amchart('ChartCC',
                          $atts,
                          ['k' => 'name',
                           'v' => 'countCC',
                           's' => 'flag']);
    WD_message('exit');
    return $reply;
}

/**
 * [ ChartBrowsers b1="Firefox" v1=22, b2=Safari... ]
 */
function WD_shortcode_ChartBrowsers($atts, $content=null, $tag='' ) {
    WD_message('entry');
    $reply = YB_envoce_amchart('ChartBrowsers',
                          $atts,
                          ['k' => 'browser',
                           'v' => 'value']);
    WD_message('exit');
    return $reply;
}

/**
 * [ ChartBrowsers b1="Firefox" v1=22, b2=Safari... ]
 */
function WD_shortcode_ChartOS($atts, $content=null, $tag='' ) {
    WD_message('entry');
    $reply = YB_envoce_amchart('ChartOS',
                          $atts,
                          ['k' => 'os',
                           'v' => 'value']);
    WD_message('exit');
    return $reply;
}

/**
 * [ ChartUsers n1="John" n2=... v1=22 v2=...]
 */

function WD_shortcode_ChartUsers($atts, $content=null, $tag='' ) {
    WD_message('entry');
    $reply = YB_envoce_amchart('ChartUsers',
                          $atts,
                          ['n' => 'name',
                           'v' => 'value',
                           'i' => 'photo',
                          ]);
    WD_message('exit');
    return $reply;
}

/**
 *
 */
function repacker($codes, $atts, $defaults=['id'=>1]) {
    if (empty($atts)) return [];
    WD_message('entry');
    $level = 'debug';
    WD_message(var_export($atts,true));
    
    $keys = array_keys($codes);
    $pivot_code = preg_replace('/[0-9]*/', '', array_shift($keys));
    $valid_keys = '['.join("",array_keys($codes)).']*';
    WD_message("valid_keys = $valid_keys, pivot='$pivot_code'", $level);

    // Repack arguments, set them as amcharts want
    foreach ($atts['data'] as $kk=>$vv) {
        if (preg_match("/^($valid_keys)([0-9N]*)$/", $kk, $match)) {
            ${$match[1]}[$match[2]] = ($match[1] === 'v' ? (int)$vv : $vv); // value
        } else {
            WD_message(($msg = "Wrong argument '$kk=$vv', chart ignored"), $level);
            WD_message('exit');
            return "<p style='font_color:red'>$msg</p>";
        }
    }

    $data = [];
    if (!empty($$pivot_code)) {
        foreach (array_keys($$pivot_code) as $kk) {
            $a = [];
            foreach(array_keys($codes) as $item) {
                $a[$c=$codes[$item]] = in_array($c,['photo','flag']) && !is_array(@$$item[$kk])
                     ? ['src' => YB_get_template_file_uri($c."s/".my_slug($$pivot_code[$kk].".png"), true)]
                     : $$item[$kk];
            }
            $data[] = $a;
        }
    } else {
        WD_message("No data found", 'warn');
    }
    $reply = ['id'    => $atts['id'],
              'title' => $atts['title'],
              'data'  => $data];
    WD_message("reply=".joinX($reply));
    WD_message('exit');
    return $reply;
}

/**
 *
 */
function WD_get_data($type, $chart_id) {
    global $wpdb, $fillerCount, $dejavu_logs, $notLocal;

    WD_message('entry');

    $logsTitle = "Default title from ".__function__."($type)";
    $data  = [];
    
    // Collect records from known users
    $known_users = $logins = [];    
    if (!defined('Users')) define('Users',DB_NAME .'.'. $wpdb->prefix.'users');
    foreach(wddb->get_results($sql="SELECT * FROM ".WDvisits." AS v LEFT JOIN ".Users." AS u ON v.user_id=u.ID WHERE ".MY_SITE." GROUP BY u.ID") as $r) {
	$known_users[$r->user_id] = ($r->user_id?$r->display_name:'?');
    }

    foreach(wddb->get_results("SELECT * FROM ".Users) as $r) {
	if (WD_user_not_monitored($r)) continue;
        $known_users[$r->ID] = $r->display_name;
        $logins[$r->ID] = $r->user_login;
    }
    if (!$dejavu_logs++) { WD_message("$type: known_users=" . join(', ',array_values($known_users))); }

    $res=wddb->get_results("SELECT COUNT(*) AS total_visits, ".
			   "UNIX_TIMESTAMP(MIN(time)) AS t_fr, UNIX_TIMESTAMP(MAX(time)) AS t_to FROM ".WDvisits." WHERE ".MY_SITE);
    $gen = array_pop($res);
    if ( $e = wddb->last_error ) {
        WD_message("($type) $e", "warn");
        WD_message("($type) wpdb error: $e", "warn");
    }
    if (empty($gen)) return [];
    /**
     */
    $filler = function(&$data, $key, $value) {
        global $fillerCount;
        $fillerCount++;
        $data["k$fillerCount"] = $key;
        $data["v$fillerCount"] = $value;
    };

    /**
     */
    $translator = function ($text) {
	$tr = ['Jan'=>'Января',
	       'Feb'=>'Февраля',
	       'Mar'=>'Марта',
	       'Apr'=>'Апреля',
	       'May'=>'Мая',
	       'Jun'=>'Июня',
	       'Jul'=>'Июля',
	       'Aug'=>'Августа',
	       'Sep'=>'Сентября',
	       'Oct'=>'Октября',
	       'Nov'=>'Ноября',
	       'Dec'=>'Декабря'];
	return str_replace(array_keys($tr),array_values($tr),$text);
    };

    /**
     */
    $getChartData = function($pivot) {
	global $notLocal;
	$results = wddb->get_results($sql="SELECT $pivot,user_id,concat_ws('|',time,uri,$pivot) AS stamp,count(*) AS count ".
					  " FROM wd_visits ".
					  " LEFT JOIN wd_remotes ON remote=r_remote ".
					  " WHERE user_id>0 AND ".MY_SITE." AND $notLocal AND remote != '46.252.8.1' ".
					  " GROUP BY $pivot");
	$results = wddb->get_results($sql="SELECT $pivot,user_id,concat_ws('|',time,uri,$pivot) AS stamp,count(*) AS count ".
					  " FROM wd_visits ".
					  ($pivot=='r_country' ? " LEFT JOIN wd_remotes ON remote=r_remote " : "").
					  " WHERE user_id>0 AND ".MY_SITE." AND $notLocal AND remote != '46.252.8.1' ".
					  " GROUP BY $pivot");
	WD_message("getChartData($pivot) $sql)", "green");
	// Massage the reply, merge multiple answers (the snipped was provided by Claude AI)
	$grouped = [];
	foreach ($results as $item) {
            $key = $item->$pivot;
            if (!isset($grouped[$key])) {
		$grouped[$key] = clone $item;
            } else {
		$grouped[$key]->user_id = max($grouped[$key]->user_id, $item->user_id);
		$grouped[$key]->count  += $item->count;
            }
	    unset($grouped[$key]->stamp);
	}
	//echo "<pre style=font-size:x-small>";print_r($results);echo"</pre>";
	//echo "<pre style=font-size:x-small>";print_r(array_values($grouped));echo"</pre>";
	return (array_values($grouped));
    };
    
    switch(preg_replace('/[0-9]*$/', '', $type)) {    

	case 'ChartUsers':
	    foreach(wddb->get_results($sql="SELECT user_id,count(*) AS visits  FROM wd_visits WHERE ".MY_SITE." GROUP BY  user_id") as $r){
 		foreach($getChartData('user_id') as $counter=>$r) {
		    if (WD_user_not_monitored($r)) continue;
                    $data["n$counter"] = ($login=WD_display_name($r->user_id,'user_login')); // ('id',$r->user_id)->user_login);
                    $data["v$counter"] = $r->count;
                    $data["i$counter"] = ['src' => YB_get_template_file_uri("photos/$login.png", true)];
		}
	    }
	    WD_message($sql,'blue');
            $logsTitle = sprintf("%s ÷ %s %s",
				 $translator(date('j M Y',$gen->t_fr)), $translator(date('j M Y',$gen->t_to)),
				 //$gen->total_visits, date('Y-m-d',$gen->t_fr), (date('Y-m-d',$gen->t_to)),
				 (PRODUCTION_MODE ? "" : " (debug)"));
	    break;
	    
	case 'ChartCC':
/*
	    //SELECT user_id,concat_ws('|',time,uri,remote) AS stamp,count(*) AS count  FROM wd_visits WHERE user_id>0 AND uri REGEXP '^/(adb|restor)/[a-z0-9]* /$' AND remote != '127.0.0.1' GROUP BY user_id
//SELECT r_country,user_id,concat_ws('|',time,uri,remote) AS stamp,count(*) AS count  FROM wd_visits LEFT JOIN wd_remotes ON remote=r_remote WHERE user_id>0 AND uri REGEXP '^/(adb|restor)/[a-z0-9]* /$' AND remote NOT IN  ('46.252.8.1','127.0.0.1') GROUP BY user_id;
            //foreach (wddb->get_results($q="SELECT r_country, COUNT(*) as count FROM ".WDvisits." LEFT JOIN wd_remotes ON r_remote=remote WHERE ".MY_SITE." GROUP BY r_country") as $r) {
	    foreach (wddb->get_results($sql="SELECT r_country,user_id,concat_ws('|',time,uri,remote) AS stamp,count(*) AS count ".
					    " FROM wd_visits ".
					    " LEFT JOIN wd_remotes ON remote=r_remote ".
					    " WHERE user_id>0 AND ".MY_SITE." AND $notLocal AND remote != '46.252.8.1' ".
					    " GROUP BY r_country") as $r) {
		WD_message($sql, 'blue');
		WD_message(joinX($r));
            WD_message("$r->count $r->r_country");
*/
 	    foreach($getChartData('r_country') as $counter=>$r) {	    
		$data["k$counter"] = str_replace(" ","\n",($country=(empty($c=$r->r_country) ? 'localhost' : $c)));
		$data["v$counter"] = $r->count;
		$data["s$counter"] = ['src' => YB_get_template_file_uri("flags/".my_slug("$country.png"), true)];
            }
            WD_message("($type) ".var_export($data,true));
            $logsTitle = "Из каких стран смотрят ".(PRODUCTION_MODE ? "" : " (debug)");
	    break;
            
	case 'ChartBrowsers':
            foreach (wddb->get_results($q="SELECT user_agent,  COUNT(*) as count FROM ".WDvisits." WHERE ".MY_SITE." GROUP BY user_agent") as $r) {
		if (empty($countBR=@$dejavu[$browser=wd_getBrowser($r->user_agent)])) {
                    $dejavu[$browser] = $countBR = ++$fillerCount;
                    $data["k$countBR"] = $browser;
                    $data["v$countBR"] = $r->count;
		} else {
                    $data["v$countBR"] += $r->count;
		}
            }
            $logsTitle = "Какой браузер" . (PRODUCTION_MODE ? "" : " (debug)");
            break;
	    
	case 'ChartOS':
            //$data = ['k1'=>'Mac', 'v1'=>10, 'k2=>Windows', 'v2'=>7];
            $dejavu = [];
            foreach (wddb->get_results('SELECT user_agent,  COUNT(*) as count FROM '.WDvisits.' WHERE '.MY_SITE.' GROUP BY user_agent') as $r) {
		if (empty($countOS = @$dejavu[$os=wd_getOS($r->user_agent)])) {
                    $dejavu[$os] = $countOS = ++$fillerCount;
                    $data["k$countOS"] = $os;
                    $data["v$countOS"] = $r->count;
		} else {
                    $data["v$countOS"] += $r->count;
		}
            }
            $logsTitle = 'Какой компьютер' . (PRODUCTION_MODE ? "" : " (debug)");
            break;
            
	case 'ChartTimes':
	    $times = [];
            foreach (wddb->get_results("SELECT time  FROM ".WDvisits." WHERE ".MY_SITE) as $r) @$times[date('H'.':00', strtotime($r->time))]++;
	    ksort($times);
	    foreach($times as $key=>$value) {
		@++$counter;
		$data["k$counter"] = $key;
		$data["v$counter"] = $value;
	    }
	    WD_message('ChartTimes: '.joinX($data));
	    $logsTitle = "В какое время дня смотрят " . (PRODUCTION_MODE ? "" : " (debug)");
	    break;
	    
	case 'ChartPages':
            foreach (wddb->get_results("SELECT uri,  COUNT(*) as count FROM ".WDvisits." WHERE ".MY_SITE." GROUP BY uri") as $r) {
		if (!empty($r->uri)) {
		    if (in_array(trim($r->uri,'/'), VALID_URI)) {
			$filler($data, 'Home Page', $r->count);
		    } elseif ($page = wddb->get_row("SELECT post_title FROM ".DB_NAME.".wp_posts WHERE post_type='page' AND post_name = '".basename($r->uri)."'")) {
			$filler($data, preg_replace("/#.*/","",wordwrap($page->post_title,35,'#')), $r->count);
		    }
		}
	    }
	    $logsTitle = "Что смотрят " . (PRODUCTION_MODE ? "" : " (debug)");
	    break;
	    
	default:
    }

    $reply = ['id'    => $chart_id,
	      'title' => $logsTitle,
	      'data'  => $data];
    //echo"<pre>";print_r($reply);echo"</pre>";
    WD_message("($type) title = \"$logsTitle\"");
    WD_message("($type) args = ".var_export($data,true));
    WD_message('exit');
    return $reply;
}
