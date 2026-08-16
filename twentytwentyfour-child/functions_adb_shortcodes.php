<?php
/**
 *
 */

/**
 * [hover_tooltips text]
 */
function YB_shortcode_hover_tooltips($atts, $content=null, $tag='' ) {
    static $codes = ['Александровского' => 'В 1910-20 годы это был Гофицкий район',];
    YB_message('entry');
    wp_enqueue_script('hover_tooltips', YB_get_template_file_uri("js/hover_tooltips.js"), []);
    $reply = '????';
/*
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
*/
    YB_message('exit');
    return join("\n",$reply);
}
add_shortcode('hover_tooltips','YB_shortcode_hover_tooltips');
