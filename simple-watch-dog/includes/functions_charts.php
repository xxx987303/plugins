<?php
/**
 * Watchdog charts
 */

define('LOCALHOSTs', ['127.0.0.1', '::1', 'localhost']);
//define('amchars_location', __dir__ . '/../js/');

/*
 * Fire shortcodes
 */
add_shortcode( 'ChartBrowsers','WD_shortcode_ChartBrowsers');
add_shortcode( 'ChartUsers',   'WD_shortcode_ChartUsers');
add_shortcode( 'ChartPages',   'WD_shortcode_ChartPages');
add_shortcode( 'ChartCC',      'WD_shortcode_ChartCC');
add_shortcode( 'ChartOS',      'WD_shortcode_ChartOS');

/**
 */
function WD_user_not_monitored($r) {
    $user = get_user_by( 'login', $r->user_login );
    $not_monitored = WD_SKIP_ADMIN && (($user && $r->user_login != 'mb') ? user_can($user, 'manage_options') : false);
    if (!PRODUCTION_MODE) $not_monitored = false;
    return $not_monitored;
}

/**
 *
 */
function YB_amcharts($shortcode, $atts, $argsCodes) {
    global $dejaVu_amcharts, $chart_counter, $communicator;
    
    // Call JS
    $js = function($name) {
      //wp_enqueue_script(my_slug($name,'amcharts-'), YB_get_template_file_uri("js/amcharts_5_$name.js"), []);
	wp_enqueue_script(my_slug($name,'amcharts-'), plugin_dir_url(__FILE__) . "../js/amcharts_5_$name.js", []);
    };
	
    $x = $js('index') . $js('xy') . $js('percent') . $js("themes_Animated");
    $callingSequence = preg_replace(['/[()\',]/','/ array/','/ => /','/ \]/'],["","","=","]"],"[$shortcode ".var_export($atts,true)."]");

    if (empty($chart_counter[$shortcode])) { $chart_counter[$shortcode] = 0; }
    $ID = ++$chart_counter[$shortcode];
    $title = (empty($t=@$atts['title']) ? "Test imbedded $shortcode" : "atts[title]='$t'");
    if (isset($atts['id']))    unset($atts['id']);
    if (isset($atts['title'])) unset($atts['title']);
    
    $args = (empty($atts)
             ? WD_get_args_from_logs($shortcode, $ID)
             : ['id'   => $ID,
                'title'=> $title,
                'data' => $atts]);
    $args = repacker($argsCodes, $args);
    
    $communicator[$shortcode][$ID] = $args;
    if (empty($args['data'])) {
	$reply = current_user_can('manage_options') ? "<p>No statistics available yet for $callingSequence</p>" : '';
    } else {
        // Communicate arguments to JS 
	wp_enqueue_script('communicator', plugin_dir_url(__FILE__) . "../js/amcharts/communicator.js", ['jquery'], '1.0.0', true);
        wp_localize_script('communicator', "args", $communicator);

        // Load the executor
	wp_enqueue_script($shortcode, plugin_dir_url(__FILE__) . "../js/amcharts/$shortcode.js", ['jquery'], '1.0.0', true);
         
        $reply = "<div class='amchart_title'>".(empty($t=@$args['title'])?"":$t)."</div>\n"
               . "<div class='chart_wrapper'>".(HIDE_CHART_TEST_DIV ? "" : "<p id='test$shortcode$ID'>$callingSequence</p>")
               . "<div id='chartdiv_$shortcode$ID' class='chartdiv'></div></div>\n";
    }
    return $reply;
}

/**
 */
function WD_shortcode_ChartPages($atts, $content=null, $tag='' ) {
    WD_message('entry');
    $reply = YB_amcharts('ChartPages',
                          $atts,
                          ['k' => 'page',
                           'v' => 'value']);
    WD_message('exit');
    return $reply;
}

/**
 * [ ChartCC c1="Italy" v1=22, b2="Belgium"... ]
 */
function WD_shortcode_ChartCC($atts, $content=null, $tag='' ) {
    WD_message('entry');
    $reply = YB_amcharts('ChartCC',
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
    $reply = YB_amcharts('ChartBrowsers',
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
    $reply = YB_amcharts('ChartOS',
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
    $reply = YB_amcharts('ChartUsers',
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
    $level = 'debug';
    WD_message('entry',$level);
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
    WD_message('exit', $level);
    return $reply;
}

/**
 *
 */
function WD_get_args_from_logs($type, $ID) {
    global $wpdb, $fillerCount, $dejavu_logs;
    WD_message('entry');

    $my_site = " uri REGEXP '/".WD_HOME."/[a-zA-Z0-9]+/' ";
	
    //echo __function__."($type)<br>";
    $logsTitle = "Default title from ".__function__."($type)";
    $data = [];
    $mode = (PRODUCTION_MODE ? " mode='prod' " : "1");
    
    // Collect records from known users
    $known_users = $logins = [];    
    if (!defined('Users')) define('Users',DB_NAME .'.'. $wpdb->prefix.'users');
    foreach(wddb->get_results($sql="SELECT * FROM ".WDstats." AS s LEFT JOIN ".Users." AS u ON s.user_id=u.ID WHERE $my_site AND $mode GROUP BY s.user_id") as $r) {
	$known_users[$r->user_id] = ($r->user_id?$r->display_name:'?');
    }

    foreach(wddb->get_results("SELECT * FROM ".Users) as $r) {
	if (WD_user_not_monitored($r)) continue;
        $known_users[$r->ID] = $r->display_name;
        $logins[$r->ID] = $r->user_login;
    }
    if (!$dejavu_logs++) { WD_message("($type) known_users: " . join(', ',array_values($known_users))); }

    $results=wddb->get_results("SELECT COUNT(*) AS total_visits, ".
				"UNIX_TIMESTAMP(MIN(time)) AS t_fr, UNIX_TIMESTAMP(MAX(time)) AS t_to FROM ".WDstats." WHERE $mode AND $my_site");
    $gen = array_pop($results);
    if ( $e = wddb->last_error ) {
        WD_message("($type) $q", "warn");
        WD_message("($type) wpdb error: $e", "warn");
    }
    
    $filler = function(&$data, $key, $value) {
        global $fillerCount;
        $fillerCount++;
        $data["k$fillerCount"] = $key;
        $data["v$fillerCount"] = $value;
    };
    
    switch(preg_replace('/[0-9]*$/', '', $type)) {    
	case 'ChartUsers':
            // Title
            //        foreach(wddb->get_results("SELECT COUNT(*) AS total_visits, ".
            //                           "UNIX_TIMESTAMP(MIN(time)) AS t_fr, UNIX_TIMESTAMP(MAX(time)) AS t_to FROM ".WDstats." WHERE $mode AND $my_site") as $r) {
            $logsTitle = sprintf("%d visits from %s to %s %s",
				 $gen->total_visits, date('j M Y',$gen->t_fr), date('j M Y',$gen->t_to),
				 (PRODUCTION_MODE ? "" : " (debug)"));
            
            // Data
            foreach ($logins as $user_id=>$name) {
		foreach (wddb->get_results($sql="SELECT *, COUNT(*) as visits FROM ".WDstats." WHERE user_id = $user_id AND $my_site AND $mode") as $r) {
		    if (empty($r->visits)) continue;
                    $fillerCount++;
                    $data["n$fillerCount"] = $name;
                    $data["v$fillerCount"] = $r->visits;
                    $data["i$fillerCount"] = ['src' => YB_get_template_file_uri("photos/$name.png", true)];
		}
            }
            //var_dump($data);
            break;
	    
	case 'ChartBrowsers':
            foreach (wddb->get_results($q="SELECT user_agent,  COUNT(*) as count FROM ".WDstats.
					  " WHERE $my_site AND $mode AND user_agent IS NOT NULL GROUP BY user_agent") as $r) {
		if (empty($countBR=@$dejavu[$browser=wd_getBrowser($r->user_agent)])) {
                    $dejavu[$browser] = $countBR = ++$fillerCount;
                    $data["k$countBR"] = $browser;
                    $data["v$countBR"] = $r->count;
		} else {
                    $data["v$countBR"] += $r->count;
		}
            }
            $logsTitle = sprintf("Browsers from %s to %s %s\n",
				 date('j M Y',$gen->t_fr), date('j M Y',$gen->t_to),
				 (PRODUCTION_MODE ? "" : " (debug)"));
            $logsTitle = "Browsers";
            break;
	    
	case 'ChartOS':
            //$data = ['k1'=>'Mac', 'v1'=>10, 'k2=>Windows', 'v2'=>7];
            $dejavu = [];
            foreach (wddb->get_results($q="SELECT user_agent,  COUNT(*) as count FROM ".WDstats.
					   " WHERE $my_site AND $mode AND user_agent IS NOT NULL GROUP BY user_agent") as $r) {
		if (empty($countOS=@$dejavu[$os=wd_getOS($r->user_agent)])) {
                    $dejavu[$os] = $countOS = ++$fillerCount;
                    $data["k$countOS"] = $os;
                    $data["v$countOS"] = $r->count;
		} else {
                    $data["v$countOS"] += $r->count;
		}
            }
            $logsTitle = sprintf("OS from %s to %s %s\n",
				 date('j M Y',$gen->t_fr), date('j M Y',$gen->t_to),
				 (PRODUCTION_MODE ? "" : " (debug)"));
            $logsTitle = "OS";
            break;
            
	case 'ChartCC':
            foreach (wddb->get_results($q="SELECT remote, time, COUNT(*) as count FROM ".WDstats." WHERE $my_site AND $mode GROUP BY remote") as $r) {
		if (empty($r->remote)) continue;
		if (PRODUCTION_MODE && in_array($r->remote, LOCALHOSTs)) continue;

		if ($country =  WD_getCountry($r->remote)) {
		    if (PRODUCTION_MODE && $country == 'localhost') continue;
                    WD_message("ip='".$r->remote . "' remote=$country count=".$r->count, 'warn');
                    if (empty($counter=@$dejavu[$country])) {
			$dejavu[$country] = $counter = ++$fillerCount;
			$data["k$counter"] = $country;
			$data["v$counter"] = $r->count;
			$data["s$counter"] = ['src' => YB_get_template_file_uri("flags/".my_slug("$country.png"), true)];                
                    } else {
			$data["v$counter"] += $r->count;
                    }
		} else {
                    WD_message("($type) WD_getCountry fails for \"".$r->remote."\"", 'warn');
		}
            }
            WD_message("($type) ".var_export($data,true));
            $logsTitle = sprintf("Countries %s - %s %s",
				 date('j M Y',$gen->t_fr), date('j M Y',$gen->t_to),
				 (PRODUCTION_MODE ? "" : " (debug)"));
            break;
            
	case 'ChartPages':
	    $valid_uri = ['restor','restor_tmp','adb','adb_tmp'];
            foreach (wddb->get_results("SELECT uri,  COUNT(*) as count FROM ".WDstats." WHERE $my_site AND uri NOT REGEXP '/(test|stat)' GROUP BY uri") as $r) {
		if (!empty($r->uri)) {
		    if (in_array(trim($r->uri,'/'), $valid_uri)) {
			$filler($data, 'Home Page', $r->count);
		    } elseif ($page = wddb->get_row("SELECT post_title FROM ".DB_NAME.".wp_posts WHERE post_type='page' AND post_name = '".basename($r->uri)."'")) {
			$filler($data, preg_replace("/#.*/","",wordwrap($page->post_title,35,'#')), $r->count);
		    }
		}
	    }
	    $logsTitle = sprintf("Pages %s - %s %s\n",
				 date('j M Y',$gen->t_fr), date('j M Y',$gen->t_to),
				 (PRODUCTION_MODE ? "" : " (debug)"));
	    break;
	    
	default:
    }

    $reply = ['id'    => $ID,
	      'title' => $logsTitle,
	      'data'  => $data];
    //echo"<pre>";print_r($reply);echo"</pre>";
    WD_message("($type) title = \"$logsTitle\"");
    WD_message("($type) args = ".var_export($data,true));
    WD_message('exit');
    return $reply;
}
