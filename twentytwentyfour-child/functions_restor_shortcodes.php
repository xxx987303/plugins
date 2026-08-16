<?php
/**
 * Make WP user-friendly and add missing features to WP_Carousel plugin
 */

if (!defined('CLI_MODE'))        define('CLI_MODE', empty($_SERVER['HTTP_HOST']));
if (!defined('PRODUCTION_MODE')) define('PRODUCTION_MODE', false);
define('NO_FIX_METADATA', true);
define('NO_DIFF', true);
define('HIDE_CHART_TEST_DIV', PRODUCTION_MODE);
require_once ABSPATH . '/wp-content/plugins/wp-watch-dog/includes/functions.php';

/**
 * Start output buffering
 */
function YB_start_output_buffering() {
    if (!PRODUCTION_MODE) echo "\n<!-- ".__function__." -->\n";
    ob_start();
}
if (!CLI_MODE) { add_action('wp_head', 'YB_start_output_buffering'); }

/**
 * End output buffering, get the content, modify it, and then output it
 */
function YB_end_output_buffering() {
    global $diff_metadata;
    
    if (!PRODUCTION_MODE) echo "\n<!-- ".__function__." -->\n";
    IF (!NO_FIX_METADATA) YB_fix_metadata();
  
    $content_parts = preg_split(";<main|</main>;", ob_get_clean());
    $head    = $head_before = str_replace("\n", ($CR = " CR_RT "), $content_parts[0]); 
    $content = $content_before = preg_replace([";\n;", ";>\s*?<;"], ["", "> <"], '<main' . $content_parts[1] . '</main>');
    
    // Mofify the content, moustly impose captions
    if (!VANILLA_OUTPUT) {
        $head    = YB_strip_images_url($head);
        $content = YB_figcaption_to_media($content);
        $content = YB_recover_carousel_captions($content);
        $content = YB_strip_images_url($content);
    }
    
    // See the difference between modified and original pages
    if (!PRODUCTION_MODE && !NO_DIFF) {
        $showDiff = function($title, $content, $old, $new="") {
            return (($diff = (empty($new) ? $old : pb_htmlDiff($old, $new)))
                    ? str_replace("</main>",
                                  "<div class='yb-diff'><h3>$title</h3>\n$diff\n</div><br>\n</main>",
                                  $content)
                    : $content);
        };
        $content = $showDiff("Diff main section", $content, $content_before, $content);
        $content = $showDiff("Diff head section", $content, $head_before, $head);
        $content = $showDiff("Diff DB metadata (first 3 records)", $content, $diff_metadata);
    }
    
    // Show comments & errors
    if (in_array('administrator', wp_get_current_user()->roles) && ($messages = YB_message('print'))) {
        $content = str_replace("</main>", "$messages\n</main>", $content);
    }
    
    // Return the tidy page if desired
    echo str_replace($CR, "\n", $head) . (TIDY_SOURCE ? getTidy($content) : $content) . $content_parts[2];
}
if (!CLI_MODE) { add_action('wp_footer', 'YB_end_output_buffering'); }

/**
 * [FBimage i="image.png" c="caption" t="text thumbnail, otherwise image" h?=?"thumbnail height"]
 * "c" has precedence over the image caption
 */
function YB_shortcode_FBimage($atts, $content=null, $tag='' ) {
    YB_message('entry');
    YB_get_atts($atts, $tag, ['i'=>'BelokopytovUs', 't'=>'']);
    $post = YB_get_media($atts['i']);
    $caption = empty($cop=@$atts['c']) ? $post['caption'] : $cop;
    $img_src = $post['url'];
    $thumbnail = YB_get_thumbnail($atts['t'], $img_src, $caption);
    YB_message('exit');
    return "<a href=\"$img_src\" class='fancybox' data-fancybox='images' data-caption='$caption'>$thumbnail</a>"; // data-type='image'
    return "<a href=\"$img_src\">$thumbnail</a>";
}

/**
 * Crafted after https://thebizpixie.com/article/display-external-url-in-popup-modal-wordpress/
 */
function YB_shortcode_FBmodal_DEV( $atts ){
    YB_message('entry');
    $args = shortcode_atts(['id'      => 'test',
                            'url'     => 'https://nordita.org',
                            'button'  => 'Click me',
                            'heading' => 'Heading',        
                            'heading_tag'     => 'h3',
                            'close_character' => '&times;',
                            'close_outside'   => 'TRUE',        
                            'class'           => ''],
                           $atts);
  //$class = $args['$class'];  
    $class = $args['class'];  
    $args['close_outside'] = preg_match("/(false|no)/i", $args['close_outside']) ? FALSE : TRUE;
    $reply = [];
    
    /*** HTML ***/
    /* Button */
    $reply[] = "<button id='modal-button-$args[id]' class='button modal__button$class'>$args[button]</button>";
    
    /* Modal */
    $reply[] = "
     <div id='modal-$args[id]' class='modal__container$class'>
      <div id='modal-content-$args[id]' class='modal__content$class'>
	   <div class='modal__header'>
		<span id='modal-close-$args[id]' class='modal__close'>$args[close_character]</span>";	
    if ($args['heading']) {
		$reply[] = "<$args[heading_tag] class='modal__heading'>$args[heading]</$args[heading_tag]>";
    }
    $reply[] = "</div>";    
    if ($args['url']) {
        $reply[] = "<iframe loading='lazy' id='modal-iframe-$args[id]' class='modal__iframe' width='100%' height='100%' src='$args[url]'></iframe>";
    }    
    $reply[] = "</div>\n</div>";
    
    /** Javascript **/
    $reply[] = "
<script type='text/javascript'>
var m_$args[id] = document.getElementById('modal-$args[id]');
var m_button_$args[id] = document.getElementById('modal-button-$args[id]');
var m_close_$args[id] = document.getElementById('modal-close-$args[id]');";
$reply[] = "console.log(m_close_$args[id]);";
   $reply[] = "m_button_$args[id].onclick = function() { m_$args[id].style.display = 'block'; }" .
              "m_close_$args[id].onclick = function() { m_$args[id].style.display = 'none'; }";
    if ($args['close_outside']) {
        $reply[] = "window.addEventListener('click', function(event) { if ( event.target == m_$args[id] ) { m_$args[id].style.display = 'none';}})";
    }
    $reply[] = "</script>\n";    

    YB_message('exit');
    return join("\n", $reply);
}

/**
 * [FBmodal2 url='url', t='click me']
 */
function YB_shortcode_FBmodal2_DEV($atts, $content=null, $tag='' ) {
    YB_message('entry');
    YB_get_atts($atts, $tag, ['url'=>'https://nordita.org', 't'=>'', 'id'=>'id']);
    $caption = $atts['url'];
    $thumbnail = YB_get_thumbnail($atts['t'], $atts['url'], $caption);
    YB_message('exit');
    return "";
    return "<iframe loading='lazy' id='modal-iframe-$atts[id]' class='modal__iframe' width='100%' height='100%' src='$atts[url]'>$thumbnail</iframe>";
    return "<a href=\"$img_src\" class='fancybox' data-fancybox='images' data-caption='$caption'>$thumbnail</a>"; // data-type='image'
    return "<a href=\"$img_src\">$thumbnail</a>";
}

/**
 * [FBmodal url='url', t='click me']
 */
function YB_shortcode_FBmodal($atts=[], $content=null, $tag='' ) {
    global $idFBmodal;

    YB_message('entry');
    if (empty ($idFBmodal)) $idFBmodal = 0;
    $atts = shortcode_atts(['url' => 'https://dn.se/',
                            't'   => 'Click me',
                            'id'  => ++$idFBmodal],
                           $atts,
                           $tag);                                                                                                            

    $reply = "<span><a data-fancybox data-type='iframe' href='$atts[url]'>".YB_get_thumbnail($atts['t'])."</a></span>\n";
    // Enqueue script
    wp_enqueue_script( 'custom-FBmodal-script', YB_get_template_file_uri('js/custom_iframeModal.js'), array(), false, true );
    YB_message('exit');
    return $reply;
}

/**
 * [FBgallery i2="i2.png" i1="i1.png".png" c2="c2" c1="c1"]
 */
function YB_shortcode_FBgallery_DEV($atts, $content=null, $tag='' ) {
    YB_message('entry');
    YB_get_atts($atts, $tag, ['t'=>'']);
    foreach ($atts as $k=>$v) {
        if (preg_match('/^([ic]*)([0-9]*)$/', $k, $match)) {
            ${$match[1]}[$match[2]] = $v;
        }
    }
    $reply = [];
    foreach ($i as $k=>$v) {
        if ($post = YB_get_media($v)) {
            $img_src = $post['url'];
            $caption = empty($cop=@$atts['c'.$k]) ? $post['caption'] : $cop;
        } else {
            YB_message("No reply from YB_get_media($v)",'warn');
            $img_src = $caption = NULL;
        }
        $reply[] = "<a href='$img_src' data-fancybox='gallery' data-caption='$caption'>" . YB_get_thumbnail($atts['t'], $img_src, $caption) . "</a>";      
    }
    YB_message('exit');
    return join("\n",$reply);
}

/**
 *
 */
function YB_amcharts($shortcode, $atts, $argsCodes) {
    global $dejaVu_amcharts, $chart_counter, $communicator;
    
    $js = function($name) {
        wp_enqueue_script(my_slug($name,'amcharts-'), YB_get_template_file_uri("js/amcharts_5_$name.js"), []);
    };
    $x = $js('index') . $js('xy') . $js('percent') . $js("themes_Animated");
    $callingSequence = preg_replace(['/[()\',]/','/ array/','/ => /','/ \]/'],["","","=","]"],"[$shortcode ".var_export($atts,true)."]");

    if (empty($chart_counter[$shortcode])) { $chart_counter[$shortcode] = 0; }
    $ID = ++$chart_counter[$shortcode];
    $title = (empty($t=@$atts['title']) ? "Test imbedded $shortcode" : "atts[title]='$t'");
    if (isset($atts['id']))    unset($atts['id']);
    if (isset($atts['title'])) unset($atts['title']);
    
    $args = (empty($atts)
             ? YB_get_args_from_logs($shortcode, $ID)
             : ['id'   => $ID,
                'title'=> $title,
                'data' => $atts]);
    $args = repacker($argsCodes, $args);

    $communicator[$shortcode][$ID] = $args;
    if (empty($args['data'])) {
        $reply = "<p>No statistics available yet for $callingSequence</p>";
    } else {
        // Communicate arguments to JS 
      //$IDs[$shortcode] = $ID;
        wp_enqueue_script ('communicator', YB_get_template_file_uri("js/amcharts/communicator.js"), []);
      //wp_localize_script('communicator', "IDs", $IDs);
        wp_localize_script('communicator', "args", $communicator);

        // Load the executor
        wp_enqueue_script ($shortcode, YB_get_template_file_uri("js/amcharts/$shortcode.js"), ['jquery'], '1.0.0', true);
         
        $reply = "<div class='amchart_title'>".(empty($t=@$args['title'])?"":$t)."</div>\n"
               . "<div class='chart_wrapper'>".(HIDE_CHART_TEST_DIV ? "" : "<p id='test$shortcode$ID'>$callingSequence</p>")
               . "<div id='chartdiv_$shortcode$ID' class='chartdiv'></div></div>\n";
    }
    return $reply;
}

function YB_shortcode_ChartPages($atts, $content=null, $tag='' ) {
    YB_message('entry');
    $reply = YB_amcharts('ChartPages',
                          $atts,
                          ['k' => 'page',
                           'v' => 'value']);
    YB_message('exit');
    return $reply;
}

/**
 * [ ChartCC c1="Italy" v1=22, b2="Belgium"... ]
 */
function YB_shortcode_ChartCC($atts, $content=null, $tag='' ) {
    YB_message('entry');
    $reply = YB_amcharts('ChartCC',
                          $atts,
                          ['k' => 'name',
                           'v' => 'countCC',
                           's' => 'flag']);
    YB_message('exit');
    return $reply;
}

/**
 * [ ChartBrowsers b1="Firefox" v1=22, b2=Safari... ]
 */
function YB_shortcode_ChartBrowsers($atts, $content=null, $tag='' ) {
    YB_message('entry');
    $reply = YB_amcharts('ChartBrowsers',
                          $atts,
                          ['k' => 'browser',
                           'v' => 'value']);
    YB_message('exit');
    return $reply;
}

/**
 * [ ChartBrowsers b1="Firefox" v1=22, b2=Safari... ]
 */
function YB_shortcode_ChartOS($atts, $content=null, $tag='' ) {
    YB_message('entry');
    $reply = YB_amcharts('ChartOS',
                          $atts,
                          ['k' => 'os',
                           'v' => 'value']);
    YB_message('exit');
    return $reply;
}

/**
 * [ ChartUsers n1="John" n2=... v1=22 v2=...]
 */
function YB_shortcode_ChartUsers($atts, $content=null, $tag='' ) {
    YB_message('entry');
    $reply = YB_amcharts('ChartUsers',
                          $atts,
                          ['n' => 'name',
                           'v' => 'value',
                           'i' => 'photo',
                          ]);
    YB_message('exit');
    return $reply;
}

/**
 *
 */
function repacker($codes, $atts, $defaults=['id'=>1]) {
    $level = 'debug';
    YB_message('entry',$level);
    YB_message(var_export($atts,true));
    
    $pivot_code = preg_replace('/[0-9]*/', '', array_shift(array_keys($codes)));
    $valid_keys = '['.join("",array_keys($codes)).']*';
    YB_message("valid_keys = $valid_keys, pivot='$pivot_code'", $level);

    // Repack arguments, set them as amcharts want
    foreach ($atts['data'] as $kk=>$vv) {
        if (preg_match("/^($valid_keys)([0-9N]*)$/", $kk, $match)) {
            ${$match[1]}[$match[2]] = ($match[1] === 'v' ? (int)$vv : $vv); // value
        } else {
            YB_message(($msg = "Wrong argument '$kk=$vv', chart ignored"), $level);
            YB_message('exit');
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
        YB_message("No data found");
    }
    YB_message('exit', $level);
    return ['id'    => $atts['id'],
            'title' => $atts['title'],
            'data'  => $data];
}

/**
 *
 */
function YB_get_args_from_logs($type, $ID) {
    global $wpdb, $fillerCount, $dejavu_logs;
    YB_message('entry');

    $logsTitle = "Default title from ".__function__."($type)";
    $data = [];
    $mode = (PRODUCTION_MODE ? " mode='prod' " : "1");

    $table_stat = $wpdb->prefix . 'wd_visitor_stats';
    $table_user = $wpdb->prefix . 'users';

    // Known users
    $known_users = $logins = [0=>'?'];    
    foreach($wpdb->get_results("SELECT * FROM $table_stat AS s LEFT JOIN $table_user AS u ON s.ID=u.ID " .
                               "WHERE $mode GROUP BY user_id") as $r) { $known_users[$r->user_id] = ($r->user_id?$r->display_name:'?'); }
    foreach($wpdb->get_results("SELECT * FROM $table_user") as $r) {
        $known_users[$r->ID] = $r->display_name;
        $logins[$r->ID] = $r->user_login;
    }
    if (!$dejavu_logs++) { YB_message("($type) known_users: " . join(', ',array_values($known_users))); }
        
    $gen = array_pop($wpdb->get_results("SELECT COUNT(*) AS total_visits, ".
                                        "UNIX_TIMESTAMP(MIN(time)) AS t_fr, UNIX_TIMESTAMP(MAX(time)) AS t_to FROM $table_stat WHERE $mode"));
    if ( $e = $wpdb->last_error ) {
        YB_message("($type) $q", "warn");
        YB_message("($type) wpdb error: $e", "warn");
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
        //        foreach($wpdb->get_results("SELECT COUNT(*) AS total_visits, ".
        //                           "UNIX_TIMESTAMP(MIN(time)) AS t_fr, UNIX_TIMESTAMP(MAX(time)) AS t_to FROM $table_stat WHERE $mode") as $r) {
        $logsTitle = sprintf("%d visits from %s to %s %s",
                             $gen->total_visits, date('j M Y',$gen->t_fr), date('j M Y',$gen->t_to),
                             (PRODUCTION_MODE ? "" : " (debug)"));
        
        // Data
        foreach ($logins as $user_id=>$name) {
            foreach ($wpdb->get_results("SELECT *, COUNT(*) as visits FROM $table_stat WHERE user_id = $user_id AND $mode") as $r) {
                $fillerCount++;
                $data["n$fillerCount"] = $name;
                $data["v$fillerCount"] = $r->visits;
                $data["i$fillerCount"] = ['src' => YB_get_template_file_uri("photos/$name.png", true)];
            }
        }
        //var_dump($data);
        break;

    case 'ChartBrowsers':
        foreach ($wpdb->get_results($q="SELECT user_agent,  COUNT(*) as count FROM $table_stat " .
                                    " WHERE $mode AND user_agent IS NOT NULL GROUP BY user_agent") as $r) {
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
        foreach ($wpdb->get_results($q="SELECT user_agent,  COUNT(*) as count FROM $table_stat " .
                                    " WHERE $mode AND user_agent IS NOT NULL GROUP BY user_agent") as $r) {
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
        foreach ($wpdb->get_results($q="SELECT remote, time, COUNT(*) as count FROM $table_stat WHERE $mode GROUP BY remote") as $r) {
            if (in_array($r->remote, ['127.0.0.1', '::1', 'localhost'])) {
                $country = 'localhost';
                if (PRODUCTION_MODE) {
                    YB_message("($type) In PRODUCTION_MODE remote='".$r->remote."'", 'warn');
                    continue;
                }
            } elseif (empty($r->remote)) {
                if ($r->time > '2024-06-27') YB_message("($type) empty remote ".$r->time, 'warn');
                continue;
            } elseif ($ipdat = @json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=".$r->remote))) {
                $country = $ipdat->geoplugin_countryName;
              //$country = str_replace(" ","\n",$country);
            } else {
                $country = null;
            }
            
            if ($country) {
                YB_message("ip='".$r->remote . "' remote=$country count=".$r->count);
                if (empty($counter=@$dejavu[$country])) {
                    $dejavu[$country] = $counter = ++$fillerCount;
                    $data["k$counter"] = $country;
                    $data["v$counter"] = $r->count;
                    $data["s$counter"] = ['src' => YB_get_template_file_uri("flags/".my_slug("$country.png"), true)];                
                } else {
                    $data["v$counter"] += $r->count;
                }
            } else {
                YB_message("($type) ipdat fails for \"".$r->remote."\"", 'warn');
            }
        }
        YB_message("($type) ".var_export($data,true));
        $logsTitle = sprintf("Countries %s - %s %s",
                             date('j M Y',$gen->t_fr), date('j M Y',$gen->t_to),
                             (PRODUCTION_MODE ? "" : " (debug)"));
        break;
        
    case 'ChartPages':
        foreach ($wpdb->get_results("SELECT uri,  COUNT(*) as count FROM $table_stat WHERE uri NOT REGEXP '/(test|stat)' GROUP BY uri") as $r) {
            if (!empty($r->uri)) {
                if ($r->uri == '/restor/') {
                    $filler($data, 'Home Page', $r->count);
                } elseif ($page = $wpdb->get_row("SELECT post_title FROM wp_posts WHERE post_type='page' AND post_name = '".basename($r->uri)."'")) {
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

    YB_message("($type) title = \"$logsTitle\"");
    YB_message("($type) args = ".var_export($data,true));
    YB_message('exit');
    return ['id'    => $ID,
            'title' => $logsTitle,
            'data'  => $data];
}

/**
 * Horisontal delimiter image replacing <hr>
 */
function YB_shortcode_separator($atts=[], $content=null, $tag='' ) {
    $post = YB_get_media('separator');
    return " <figure class=\"yb-separator-wrapper\"><img src=\"".@$post['url']."\" class=\"yb-separator\"/></figure> ";
}


/**
 * Central location to create all shortcodes.
 */
function YB_shortcodes_init() {
    if (!PRODUCTION_MODE) echo "\n<!-- ".__function__." -->\n";
    //add_shortcode( 'FBgallery',   'YB_shortcode_FBgallery_DEV' );
    //add_shortcode( 'FBmodal2',    'YB_shortcode_FBmodal2_DEV');
    add_shortcode( 'ChartBrowsers','YB_shortcode_ChartBrowsers');
    add_shortcode( 'ChartUsers',   'YB_shortcode_ChartUsers');
    add_shortcode( 'ChartPages',   'YB_shortcode_ChartPages');
    add_shortcode( 'ChartCC',      'YB_shortcode_ChartCC');
    add_shortcode( 'ChartOS',      'YB_shortcode_ChartOS');
    add_shortcode( 'FBimage',      'YB_shortcode_FBimage' );
    add_shortcode( 'FBmodal',      'YB_shortcode_FBmodal');
    add_shortcode( 'FBseparator',  'YB_shortcode_separator' );
    add_shortcode(   'separator',  'YB_shortcode_separator' );
}
if (!CLI_MODE) { add_action( 'init', 'YB_shortcodes_init' ); }

/**
 * Return the thumbnail as image OR text
 */
function YB_get_thumbnail($text_thumbnail, $img_src="", $caption="") {
    return (empty($text_thumbnail)
      //? "<figure><img src=\"$img_src\" class='fbimage-thumbnail'/><figcaption class='fbimage-thumbnail-img-caption'>$caption</figcaption></figure>"
        ? "<figure><img src=\"$img_src\" class='fbimage-thumbnail'/><div class='fbimage-thumbnail-img-caption'>$caption</div></figure>"
	: "<span class=fbimage-thumbnail-text>$text_thumbnail</span>");
}

/**
 * Apply shortcode defaults, normalize attribute keys (lowercase) 
 */
function YB_get_atts(&$atts, $tag, $force_defs=[]) {
  $defaults = $force_defs;
  foreach (array_change_key_case($atts,CASE_LOWER) as $k=>$v) {
    $defaults[$k] = "";
  }
  $atts = shortcode_atts($defaults, $atts, $tag);
}


/**
 * <div class=\"fancybox-caption fancybox-caption--separate\"><div class=\"fancybox-caption__body\">$caption</div></div>"
 */
function YB_figcaption($caption) {
    return "<div class=\"img-caption\">$caption</div>";
}

function my_slug($text, $prependix="") {
    return str_replace(' ','-',strToLower($prependix . $text));
}

/**
 * Set image caption in the media library
 */
function YB_set_caption($img, $caption) {
    if (!preg_match(';/wp-content/uploads/;', $img)) {
        YB_message("($img) NOT MY MEDIA FILE", 'warn');
        return;
    }
    if ( preg_match('/src="(.*?)"/', $img, $match)) { $img = basename($match[1]); }
    $img_slug = YB_strip_fn($img);
    $caption = trim(strip_tags($caption));
    if (preg_match('/separator/',$img_slug)) YB_message("($img_slug $caption)","warn");
    
    $post = YB_get_media($img_slug);
    if (empty($post['ID'])) { YB_message("($img_slug, $caption) Empty ID", "warn"); return; }
    if ($caption == @$post['caption']) {
        YB_message("($img_slug, $caption) ALREADY OK");
    } elseif (CLI_MODE) {
        YB_message("($img_slug, '$caption') READY FOR execution");
    } else {
        $reply = wp_update_post(['ID'           => $post['ID'],
                                 'post_excerpt' => $caption,
                                 'meta_value'   => $caption]);
        YB_message("($img_slug) SETTING '$caption', reply=$reply", "warn");
    }
}

/**
 * Store the figcaption body as post_excerpt field in the database,
 * replace figcaption by YB_figcaption()
 */
function YB_figcaption_to_media($content) {
    YB_message('entry');   
    
    $callback = function($matches) {
        if (CLI_MODE) var_dump($matches);
        $caption = trim(strip_tags($matches[3]));
        YB_set_caption($matches[2], $caption);
        $reply = $matches[1] . " " . YB_figcaption($caption);
        return $reply;
    };
    
    $pattern = '/(<img [^>]*?src=\"([^\"]*)\"[^>]*?>)[^<]*?(<figcaption.*?>.*?<\/figcaption>)/';
    $content = preg_replace_callback($pattern, $callback, $content);
    YB_message('exit');
    return $content;
}
    
/**
 * $content = preg_replace(";src=\"[htps]*://$_SERVER[HTTP_HOST]([\w\/\-]*wp-content/uploads/[^\"]*)\";", "src=\"$1\"", $content);
 */
function YB_strip_images_url($content) {
  YB_message('entry');   
  
  $pattern = ";(src|href|data-wpc_url)=([\'\"])([htps]*://$_SERVER[HTTP_HOST])?(/[\w\/\-]*wp-content/uploads/[^\"\']*)([\"\']);";
  $callback = function($matches) {
    if (CLI_MODE) var_dump($matches);
    return $matches[1] ."=". $matches[2] . YB_strip_fn($matches[4],true) . $matches[5];
  };

  $content = preg_replace_callback($pattern,
				   $callback,
				   preg_replace(";(sizes|srcset)=\"[^\"]*\";", "", $content));
  YB_message('exit');   
  return $content;
}


/**
 * Add captions to the free version of WP carousel 
 */
function YB_recover_carousel_captions($content) {
  YB_message('entry');   
  $callback = function($matches) {
    if (CLI_MODE) var_dump($matches);
    $caption = (CLI_MODE ? "CAPTION" : YB_get_caption($matches[3]));
    $type = (preg_match('/slide/', $matches[1]) ? 'caro' : 'grid');
    return (str_replace($matches[2], str_replace("<a ", "<a data-caption=\"$caption\" ", $matches[2]), $matches[0]) .			
	    ($caption
	     ? (" <div class='is-style-rounded caption-thumbnail-wpc-$type'><span class='caption-thumbnail-wpc-bkg'>".
		preg_replace('/(<br>|,).*/', '', wordwrap($caption, 40, '<br>')) . "</span></div> ")
	     : ""));
  };

  $pattern = '/<div[^>]*(swiper-slide|wpcpro-col-xs-2).*?(<a [^>]*>).*?<img[^>]*src="(.*?)"[^>]*>/';
  $content = preg_replace_callback($pattern, $callback, $content);
  YB_message('exit');   
  return $content;
}

/**
 * Fix url in the media library
 */
function YB_fix_metadata() {
    global $wpdb, $diff_metadata;
    
    YB_message('entry');
    $t_post = (CLI_MODE ? 'wp_posts'    : $wpdb->posts);
    $t_meta = (CLI_MODE ? 'wp_postmeta' : $wpdb->postmeta);
    /* wp_upload_dir() =  [ 'url'     => 'https://100.65.179.133/restor/wp-content/uploads/2024/07',
     *                      'baseurl' => 'https://100.65.179.133/restor/wp-content/uploads',
     *                      'path'    => '/Users/yb/Sites/restor/wp-content/uploads/2024/07',
     *                      'basedir' => '/Users/yb/Sites/restor/wp-content/uploads',
     *                      'subdir'  => '/2024/07',
     *                      'error'   => false ] */
    $uploads = wp_upload_dir();
    //
    // Fix post_content, clean images URL, make URLs relative, not absolute
    // This removes subjection on http/https 
    //
    $pattern = ";src=([\'\"])([^\"\']*)([\'\"]);";
    $callback = function($matches) {
        $reply = "src=$matches[1]" . YB_strip_fn($matches[2],true) . $matches[3];                                                            
        return $reply;
    };
    
    $n_updates  = 0;
    $qq = "SELECT ID,post_content FROM $t_post WHERE  post_content REGEXP 'wp-content/uploads'";
    foreach($wpdb->get_results($wpdb->prepare($qq)) as $r) {
      $post_content = preg_replace_callback($pattern, $callback, $r->post_content);
      if ($post_content != $r->post_content) {
          $wpdb->get_results($q=$wpdb->prepare($q="UPDATE $t_post SET post_content='".str_replace("'",'"',$post_content)."' WHERE ID=" . $r->ID));
          if ( $e = $wpdb->last_error ) {
              YB_message($q, "warn");
              YB_message("() wpdb error: $e", "warn");
          }
          if ($n_updates++ < 3) {
              $diff_metadata .= pb_htmlDiff($r->post_content, $post_content) . "<hr>\n";
          } else {
              //YB_message("$n_updates updates of post_content field", "warn"); 
              //YB_message('exit'); return;
          }
      }
    }
    YB_message("$n_updates updates of post_content field", ($n_updates?"warn":"debug")); 
    
    //
    // Fix file names
    //
    $qq = "SELECT ID,guid,meta_id,meta_key,meta_value FROM $t_post AS p " .
        " LEFT JOIN $t_meta AS m ON p.ID = m.post_id WHERE meta_key='_wp_attachment_metadata' AND post_type = 'attachment' "
        //        . " AND guid REGEXP '/c'"
        ;
    // YB_message($qq);
    foreach($wpdb->get_results($wpdb->prepare($qq)) as $r) {
        if ( $e = $wpdb->last_error ) {
            YB_message($qq, "warn");
            YB_message("() wpdb error: $e", "warn");
        }
        //
        // wp_postmeta
        //
        $meta_value = unserialize($r->meta_value);
        if (!file_exists($f="$uploads[basedir]/$meta_value[file]")) {
            YB_message("???????????????????? Missing file $f", "warn");
            if (file_exists($f="$uploads[basedir]/" . ($replacement=YB_strip_fn($meta_value['file'], true)))) {
                YB_message("Force $replacement", "warn");
                $meta_value['file'] = $replacement;
            } else {
                continue;
            }
        }
        
        foreach($meta_value['sizes'] as $size=>$data) {
            $meta_value['sizes'][$size]['file'] = basename($meta_value['file']);
        }
        if ($do_meta=($new_meta = serialize($meta_value)) != $r->meta_value) {
            if (false) {
                $x = "&nbsp;&nbsp;&nbsp;";
                echo "$meta_value[file]<br>";
                foreach($meta_value as $k=>$v) {
                    if (is_array($v)) {
                        echo "$x   k=$k  []<br>";
                        foreach($v as $k2=>$v2) {
                            echo "$x$x     $k2  ".var_export($v2, true)."<br>";
                        }
                        if (isset($v['file'])) { echo "$v[file]<br>".YB_strip_fn($v['file'],true)."<br><br>"; }
                    } else {
                        echo "$x k=$k  $v<br>";
                    }
                }
            }
            $q="UPDATE $t_meta SET meta_value = '$new_meta' WHERE meta_id=".$r->meta_id;
            $wpdb->get_results($wpdb->prepare($q));
            YB_message($q, 'warn');
	    if ( $e = $wpdb->last_error ) {
	    	YB_message($q, "warn");
		YB_message("() wpdb error: $e", "warn");
	    }
	} else {
            // YB_message("OK meta_value");
        }
        
        //
        // wp_posts
        //
        if ($ok_posts=($r->guid == YB_strip_fn($r->guid, true))) {
            // YB_message("OK guid");
        } else {
            $q = "UPDATE $t_post SET guid = '".YB_strip_fn($r->guid, true)."' WHERE ID=".$r->ID;
            $wpdb->get_results($wpdb->prepare($q));
            YB_message("(".$r->ID.") $q", 'warn');
	    if ( $e = $wpdb->last_error ) {
	      YB_message($q, "warn");
	      YB_message("() wpdb error: $e", "warn");
	    }
        }
        if ($ok_posts && !$do_meta) {
            YB_message("OK ".$meta_value['file']);
        } else {
            //YB_message('exit');      return;
        }
    }
    YB_message('exit');
    return;
}

/**
 * Return meta attributes for images from media library
 */
function YB_get_media($fnP) {
  global $wpdb, $cache;
  $fn = $fnP;
  // Sanity
  if (preg_match('/^https?:/', $fn) && !preg_match(';/uploads/;', $fn)) {
    YB_message("($fn) ???????????", 'warn');
    return [];
  }
  //YB_message('entry');
  if (preg_match(";src=[\'\"]?(.*?/uploads/.*?)[\'\"]?;", $fn, $match)) { $fn = $match[1]; }
  $slug = YB_strip_fn($fn);
  
  if (isset($cache[$slug])) {
    //YB_message("($slug) cached");
  } else {
    $db = (CLI_MODE ? 'wp_posts' : $wpdb->posts);
    if (CLI_MODE) { YB_message("($fnP)"); return; }
    // YB_message("($fnP) OK");
    $result = $wpdb->get_results($q=$wpdb->prepare("SELECT * FROM $db WHERE post_type='%s' AND post_name REGEXP '^$slug' ORDER BY post_date DESC LIMIT 1",
                                                   "attachment"));
    // $slug));
    if ( $e = $wpdb->last_error ) {
        YB_message($q, "warn");
      YB_message("() wpdb error: $e", "warn");
    }
    $cache[$slug] = (empty($result[0])
			 ? []
			 : ['ID'      => $result[0]->ID,
			    'name'    => $result[0]->post_name,
			    'caption' => $result[0]->post_excerpt,
			    'url'     => $result[0]->guid,
			    //'url'     => preg_replace("/^https?:/", (@$_SERVER['HTTPS']=='on' ? 'https:' : 'http:'), $result[0]->guid),
			    'ext'     => str_replace('image/', '', $result[0]->post_mime_type),]);
    if (empty($cache[$slug])) { YB_message("($fnP) Empty result", 'warn'); }
    else { YB_message("($fnP) " .$cache[$slug]['url'] . " \"" . $cache[$slug]['caption'] . "\""); }
  }
  //YB_message('exit');
  return $cache[$slug];
}
    
/**
 * $path_parts = ['dirname'
 *                'basename',
 *                'extension',
 *                'filename']
 */
function YB_strip_fn($fn, $keep_extension=false, $strip_http=true) {
  $path_parts = pathinfo($fn);
  //  YB_message("($fn) " . var_export($path_parts,true));
  $reply = preg_replace(['/(-\d*)?-[0-9]*x[0-9]*$/','/-scaled.*/','/-[0-9]+$/'], '', $path_parts['filename']);
  if ($keep_extension) {
    $path_parts['extension'] = empty($e=@$path_parts['extension']) ? "" :".$e";
    $reply = "$path_parts[dirname]/$reply$path_parts[extension]";
    if ($strip_http) $reply = preg_replace(";^[htps]+://[^\/]*/;", "/", $reply);
  }
  if ($reply != $fn) YB_message("($fn) $reply");
  return $reply;
}

/**
 */
function YB_get_caption($fn) {
  if ($post = YB_get_media($fn)) {
    $caption = @$post['caption'];
  } else {
    YB_message("($fn) YB_get_media($fn) returns no reply", "warn");
    $caption = NULL;
  }
  return $caption;
}

/**
 **/
function YB_in_list($pages=[]) {
  if (!($ok = CLI_MODE)) {
    foreach ($pages as $p) if (is_page($p)) $ok = true;
    return true;
  }
  return $ok;
}
