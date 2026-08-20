<?php
/**
 * Make WP user-friendly and add missing features to WP_Carousel plugin
 */

if (!defined('CLI_MODE'))        define('CLI_MODE', empty($_SERVER['HTTP_HOST']));
if (!defined('PRODUCTION_MODE')) define('PRODUCTION_MODE', false);

//require_once ABSPATH . '/wp-content/plugins/wp-watch-dog/includes/functions.php';

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
    add_shortcode( 'FBimage',      'YB_shortcode_FBimage' );
    add_shortcode( 'FBmodal',      'YB_shortcode_FBmodal');
    add_shortcode( 'FBseparator',  'YB_shortcode_separator' );
    add_shortcode(   'separator',  'YB_shortcode_separator' );
}
if (!CLI_MODE) { add_action( 'init', 'YB_shortcodes_init' ); }

/**
 * Return the thumbnail as image OR text
 */
/*
function YB_get_thumbnail($text_thumbnail, $img_src="", $caption="") {
    return (empty($text_thumbnail)
      //? "<figure><img src=\"$img_src\" class='fbimage-thumbnail'/><figcaption class='fbimage-thumbnail-img-caption'>$caption</figcaption></figure>"
        ? "<figure><img src=\"$img_src\" class='fbimage-thumbnail'/><div class='fbimage-thumbnail-img-caption'>$caption</div></figure>"
	: "<span class=fbimage-thumbnail-text>$text_thumbnail</span>");
}
 */

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
