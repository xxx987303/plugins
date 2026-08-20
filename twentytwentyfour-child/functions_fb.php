<?php
/**
 * Make WP user-friendly and add missing features to WP_Carousel plugin
 */

if (!defined('CLI_MODE'))        define('CLI_MODE', empty($_SERVER['HTTP_HOST']));
if (!defined('PRODUCTION_MODE')) define('PRODUCTION_MODE', false);
define('NO_FIX_METADATA', true);
define('NO_DIFF', true);
define('HIDE_CHART_TEST_DIV', PRODUCTION_MODE);
//require_once ABSPATH . '/wp-content/plugins/wp-watch-dog/includes/functions.php';

/**
 * Hover tooltips
 */
function my_keyword_tooltips() {
    if (file_exists($js=get_template_directory_uri() . '/js/keyword-tooltips.js')) {
	wp_enqueue_script(
            'keyword-tooltips',
            $js,
            [],
            '1.0',
            true  // load in footer
	);
    }
}
add_action( 'wp_enqueue_scripts', 'my_keyword_tooltips' );

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
 * Return the thumbnail as image OR text
 */
function YB_get_thumbnail($text_thumbnail, $img_src="", $caption="") {
    return (empty($text_thumbnail)
      //? "<figure><img src=\"$img_src\" class='fbimage-thumbnail'/><figcaption class='fbimage-thumbnail-img-caption'>$caption</figcaption></figure>"
        ? "<figure><img src=\"$img_src\" class='fbimage-thumbnail'/><div class='fbimage-thumbnail-img-caption'>$caption</div></figure>"
	: "<span class=fbimage-thumbnail-text>$text_thumbnail</span>");
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
//  if ($reply != $fn) YB_message("($fn) $reply");
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
