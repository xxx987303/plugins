<?php
/**
 * As is...
 */
require_once __dir__ . '/functions_fb.php';
require_once __dir__ . '/functions_shortcodes.php';

if (!defined('PRODUCTION_MODE')) define('PRODUCTION_MODE', false);
if (!defined('AFTER_LOGIN'))     define('AFTER_LOGIN', 'stat/'); // about/

if (!defined('VANILLA_OUTPUT'))  define('VANILLA_OUTPUT', false); // MUST BE 'FALSE'... Apply or not custom changes (VANILLA is "without my changes") 
if (!defined('FORCE_AUTH'))      define('FORCE_AUTH',   PRODUCTION_MODE);// Force or not "only authenticated users"
if (!defined('TIDY_SOURCE'))     define('TIDY_SOURCE', !PRODUCTION_MODE);// Tidy source is not recommended for production

/**
 *   PhotoSwipe
 **/
function adb_enqueue_photoswipe() {
    if (1) {
	wp_enqueue_script('photoswipe',           get_stylesheet_directory_uri() . '/photoswipe/photoswipe.umd.min.js',         [],                      null, [] );
	wp_enqueue_script('photoswipe-lightbox',  get_stylesheet_directory_uri() . '/photoswipe/photoswipe-lightbox.umd.min.js',['photoswipe'],          null, [] );
    } else {
	//wp_enqueue_script_module('photoswipe',           get_stylesheet_directory_uri() . '/photoswipe/photoswipe.esm.min.js',         [],                      null, [] );
	//wp_enqueue_script_module('photoswipe-lightbox',  get_stylesheet_directory_uri() . '/photoswipe/photoswipe-lightbox.esm.min.js',['photoswipe'],          null, [] );
	wp_enqueue_script_module('init-esm',       get_stylesheet_directory_uri() . '/photoswipe/init.esm.js',                   ['photoswipe-lightbox'], null, [] );
    }
    wp_enqueue_script('photoswipe-init',          get_stylesheet_directory_uri() . '/photoswipe/init.umd.js',                   ['photoswipe-lightbox'], null, [] );
    wp_enqueue_style ('photoswipe-css',           get_stylesheet_directory_uri() . '/photoswipe/photoswipe.css');
    
    // echo __function__.' ended<br>';
}
// Commiting defeat...
// Replaced by plugin, , AI help was interesting, but misleading...
// add_action('wp_enqueue_scripts', 'adb_enqueue_photoswipe');

/**
 * Fix for the search
 */
add_filter( 'relevanssi_search_ok', function( $ok, $query ) {
    if ( ! empty( $query->query_vars['s'] ) ) {
        $ok = true;
    }
    return $ok;
}, 10, 2 );


/**
 * Add dev. comments
 * level might be debug or warn
 */
function YB_message($textP='', $level='debug') {
    global $YB_messages, $YB_messages_indent;

    //if ($textP=='exit')echo"<br>";elseif($textP!='entry')echo "<span style='font-size: small'>$textP</span><br>";
    
    if (empty($YB_messages)) return "";
    if (empty($textP)) $textP = "";
    if ($textP == 'print'){
        if (CLI_MODE) {
            echo "\n\nMessages\n--------\n";
            echo str_replace("<CR>","\n",join("\n",($YB_messages)))."\n";
        }else {
            return "<div class='yb-comments'><h3>Messages...</h3><code>".join('<br>',$YB_messages)."</code></div>\n";
        }
    } elseif (!PRODUCTION_MODE || ($level == 'warn' && in_array('administrator', wp_get_current_user()->roles))) {
        $indent = (CLI_MODE ? '  ' : '&nbsp;&nbsp;');
        $text = $textP;
        if (empty($YB_messages_indent)) { $YB_messages_indent = ""; }
        if ($textP == 'exit') $YB_messages_indent = preg_replace("/^$indent/", '', $YB_messages_indent);
        if (in_array($textP, ["entry","exit"])){ $color = 'blue'; $text = "($text)"; }
        elseif ($level != 'debug')             { $color = 'red'; }
        else                                   { $color = '#000000';}
        $caller = debug_backtrace()[1]['function'];
        if (!preg_match('/^\(/', $text) && ($caller != '{closure}')) $text = "() $text";
        $text = $caller . $text;
        $msg = (CLI_MODE ? $text : "<span style='color:$color'>" . preg_replace(['/</', '/>/'], ['&lt;', '&gt;'], $YB_messages_indent . $text) . "</span>");
        if (empty($YB_messages)) $YB_messages = [];
        if (CLI_MODE)  { echo "$msg\n"; }
        else        { $YB_messages[] = $msg; }
        if ($textP == 'entry') { $YB_messages_indent .= $indent; }
    }
    return "";
}


//if (!CLI_MODE) {

/**
 * If a local avatar exists, then use it.
 * Otherwise a avatar will be used, which can be from gravatar 
 */
add_filter( 'get_avatar', 'YB_get_avatar', 10, 5 );
function YB_get_avatar( $avatar = '', $id_or_email=1, $size = 96, $default = '', $alt = '' ) {
    YB_message('entry');
    if(0)if (($id_or_email !== 1) && ($image = YB_get_template_file_uri("photos/$id_or_email.png", true))) {
        $avatar = "<img alt='$alt' src='$image' class='avatar avatar-{$size} photo' height='{$size}' width='{$size}' />";
    }
    YB_message("avatar $avatar");
    YB_message('exit');
    return $avatar;
}

/**
 * Remove posts, leave pages only
 */
function remove_posts_menu() {
    remove_menu_page('edit.php');
}
add_action('admin_menu', 'remove_posts_menu');

/**
 * Remove posts, leave pages only
 */
function remove_posts_from_admin_bar($wp_admin_bar) {
    $wp_admin_bar->remove_node('new-post');
}
add_action('admin_bar_menu', 'remove_posts_from_admin_bar', 999);

/**
 */
function YB_get_template_file_uri($file, $stripIt=false) {
    $url = get_template_directory_uri();
    if (preg_match(';\-child/;', __file__)) $url .= '-child';
    return ($stripIt ? YB_strip_fn("$url/$file",true) : "$url/$file");
}
add_action("wp_print_styles","YB_wp_print_styles"); function YB_wp_print_styles() {if(!PRODUCTION_MODE) echo "\n<!-- ".__function__." -->\n";};

/**
 * Close access for non-registered users
 */
function YB_restrict_access_to_authenticated_users() {
    if(!PRODUCTION_MODE) echo "\n<!-- ".__function__." -->\n";
    if (!is_user_logged_in() && !is_page('login')) {
	wp_redirect(wp_login_url());
        exit;
    }
}
if (FORCE_AUTH) { add_action('template_redirect', 'YB_restrict_access_to_authenticated_users'); }

/**
 * Redirect to a custom page after the login
 */
function YB_login_redirect($redirect_to, $request, $user) {
    if(!PRODUCTION_MODE) echo "\n<!-- ".__function__." -->\n";
    // Check if the user is a valid object and not an error
    if (isset($user->roles) && is_array($user->roles)) {
        return (in_array('administrator', $user->roles)
            ? admin_url()
            : home_url(AFTER_LOGIN));
    }
    return $redirect_to;
}
if (FORCE_AUTH) { add_filter('login_redirect', 'YB_login_redirect', 10, 3); }

/**
 * Create shortcode to show a page only once
 * <h1 class="top_button"><a href="about/">О чем всё это?</a></h1>
 */
function YB_shortcode_show_about($href=AFTER_LOGIN) {
    return !is_user_logged_in() && !is_page('login')
        ? "<h1 class='top_button'><a href='$href'>О чем всё это?</a></h1>" .
        "  <div style='height:100px' aria-hidden='true' class='wp-block-spacer'></div>"
        : "<div style='height:50px'  aria-hidden='true' class='wp-block-spacer'></div>";
}

/**
 * Create shortcode to display the post author
 */
function YB_shortcode_post_author() {
    if (is_singular()) {
        $post = get_post();
        if ($post) {
            $author_id = $post->post_author;
            $author_name = get_the_author_meta('display_name', $author_id);
            $author_name = get_the_author_meta('user_firstname', $author_id);
            return 'Post Author: ' . esc_html($author_name);
        } else {
            return 'Author information is not available.';
        }
    } else {
        return 'This is not a singular post.';
    }
}
if (FORCE_AUTH) {
  add_shortcode('post_author', 'YB_shortcode_post_author');
  add_shortcode('about', 'YB_shortcode_show_about');
}

/**
 */
function enqueue_custom_scripts() {
    if(!PRODUCTION_MODE) echo "\n<!-- ".__function__." -->\n";

    // Modal iFrame
    wp_enqueue_script( 'custom-hover-script', YB_get_template_file_uri('js/custom_iframeModal.js'), array(), false, true );
}
add_action( 'wp_enqueue_scripts', 'enqueue_custom_scripts' );

/**
 */
function YB_add_style_files() {
    global $styleCounter;
    if(!PRODUCTION_MODE) echo "\n<!-- ".__function__." -->\n";
    $handle = opendir(__dir__ . '/styles');
    while ($file= readdir($handle)){
        if (preg_match('/.css$/', $file)) {
            $uri = YB_strip_fn(YB_get_template_file_uri("/styles/".basename($file)), true);
            echo "<link rel='stylesheet' id='my-style-".(++$styleCounter)."' href='$uri' media='all' />\n";
/*
<style id="yb-<?php echo preg_replace(['/^\d+_/', '/\.css/', '/_/'], ['','','-'], $file); ?>" type='text/css'>
<?php require __dir__ . "/styles/$file"; ?>
</style>
*/
        }
    }
}
add_action('wp_head', 'YB_add_style_files');


/**
 * This function assumes that <body> & </body are not there...
 */
function getTidy($fn) {
    if (empty($fn)) return '';
    if (preg_match(';<body|</body;', $fn)) { die("This function assumes that <body> & </body are not there..."); }
    $tmp_file = '/tmp/shell_exec.html';
    shell_exec("rm -vf $tmp_file");
    file_put_contents($tmp_file, $fn);
    $res = shell_exec("tidy -w '<' -i $tmp_file 2>/dev/null");
    if (!empty($res)) $parts = preg_split(';<body>|</body>;', $res);
    shell_exec("rm -vf $tmp_file");
    return empty($parts[1]) ? '' : $parts[1];
}

/**
 * Paul's Simple Diff Algorithm v 0.1
 * (C) Paul Butler 2007 <http://www.paulbutler.org/>
 * May be used and distributed under the zlib/libpng license.
 *
 * This code is intended for learning purposes; it was written with short
 * code taking priority over performance. It could be used in a practical
 * application, but there are a few ways it could be optimized.
 *
 * Given two arrays, the function pb_diff will return an array of the changes.
 * I won't describe the format of the array, but it will be obvious
 * if you use print_r() on the result of a diff on some test data.
 *
 * pb_htmlDiff is a wrapper for the pb_diff command, it takes two strings and
 * returns the differences in HTML. The tags used are <ins> and <del>,
 * which can easily be styled with CSS.  
 */
function pb_diff($old, $new){
    $matrix = array();
    $maxlen = 0;
    foreach($old as $oindex => $ovalue){
        $nkeys = array_keys($new, $ovalue);
        foreach($nkeys as $nindex){
            $matrix[$oindex][$nindex] = isset($matrix[$oindex - 1][$nindex - 1])
                                      ? $matrix[$oindex - 1][$nindex - 1] + 1
                                      : 1;
            if($matrix[$oindex][$nindex] > $maxlen){
                $maxlen = $matrix[$oindex][$nindex];
                $omax = $oindex + 1 - $maxlen;
                $nmax = $nindex + 1 - $maxlen;
            }
        }   
    }
    if($maxlen == 0) return array(array('d'=>$old, 'i'=>$new));
    return array_merge(
        pb_diff(array_slice($old, 0, $omax), array_slice($new, 0, $nmax)),
        array_slice($new, $nmax, $maxlen),
        pb_diff(array_slice($old, $omax + $maxlen), array_slice($new, $nmax + $maxlen)));
}
function pb_htmlDiff($old, $new){
    $ret = '';
    $diff = pb_diff(preg_split("/[\s]+/", $old), preg_split("/[\s]+/", $new));
    $print_dots = true;
    foreach($diff as $k){
        if(is_array($k)) {
            $ret .=
                 (!empty($k['d'])?"<del style=color:red>".  preg_replace(["/</","/>/"], ["&lt;","&gt;"], implode(" ",$k['d']))."</del><br> ":'').
                 (!empty($k['i'])?"<ins style=color:green>".preg_replace(["/</","/>/"], ["&lt;","&gt;"], implode(' ',$k['i']))."</ins><br> ":'');
            $print_dots = true;
        } elseif ($print_dots) {
            $ret .= ' ... <br>';
            $print_dots = false;
            // $ret .= $k . ' ';
        }
    }
    if ($ret) { $ret .= "\n"; }
    return $ret;
}
// --------------------------------------------------------------------- hooks

add_action("document_title","YB_document_title");
function YB_document_title() {
    if (!PRODUCTION_MODE) echo "\n<!-- ".__function__." -->\n";
    //    return __function__;
}
//}

//add_action("wp_title","YB_wp_title");
//function YB_wp_title() {
//    echo "\n<!-- ".__function__." -->\n";
//    return __function__;
//}

/*
add_action("_wp_post_revision_fields","YB__wp_post_revision_fields");
function YB__wp_post_revision_fields() {echo "\n<!-- ".__function__." -->\n";}

add_action("admin_title","YB_admin_title");
function YB_admin_title() {echo "\n<!-- ".__function__." -->\n";}

add_action("dashboard_primary_title","YB_dashboard_primary_title");
function YB_dashboard_primary_title() {echo "\n<!-- ".__function__." -->\n";}

//add_action("dashboard_secondary_title","YB_dashboard_secondary_title");
//function YB_dashboard_secondary_title() {echo "\n<!-- ".__function__." -->\n";}

add_action("default_page_template_title","YB_default_page_template_title");
function YB_default_page_template_title() {echo "\n<!-- ".__function__." -->\n";}

add_action("default_title","YB_default_title");
function YB_default_title() {echo "\n<!-- ".__function__." -->\n";}

//add_action("document_title_parts","YB_document_title_parts");
//function YB_document_title_parts() {echo "\n<!-- ".__function__." -->\n";}

//add_action("document_title_separator","YB_document_title_separator");
//function YB_document_title_separator() {echo "\n<!-- ".__function__." -->\n";}

add_action("edit_form_after_title","YB_edit_form_after_title");
function YB_edit_form_after_title() {echo "\n<!-- ".__function__." -->\n";}

add_action("embed_site_title_html","YB_embed_site_title_html");
function YB_embed_site_title_html() {echo "\n<!-- ".__function__." -->\n";}

add_action("embed_thumbnail_image_shape","YB_embed_thumbnail_image_shape");
function YB_embed_thumbnail_image_shape() {echo "\n<!-- ".__function__." -->\n";}

add_action("enter_title_here","YB_enter_title_here");
function YB_enter_title_here() {echo "\n<!-- ".__function__." -->\n";}

add_action("get_the_archive_title","YB_get_the_archive_title");
function YB_get_the_archive_title() {echo "\n<!-- ".__function__." -->\n";}

add_action("get_the_archive_title_prefix","YB_get_the_archive_title_prefix");
function YB_get_the_archive_title_prefix() {echo "\n<!-- ".__function__." -->\n";}

//add_action("get_wp_title_rss","YB_get_wp_title_rss");
//function YB_get_wp_title_rss() {echo "\n<!-- ".__function__." -->\n";}

add_action("link_title","YB_link_title");
function YB_link_title() {echo "\n<!-- ".__function__." -->\n";}

add_action("list_pages","YB_list_pages");
function YB_list_pages() {echo "\n<!-- ".__function__." -->\n";}

add_action("login_headertitle","YB_login_headertitle");
function YB_login_headertitle() {echo "\n<!-- ".__function__." -->\n";}

add_action("login_title","YB_login_title");
function YB_login_title() {echo "\n<!-- ".__function__." -->\n";}

add_action("nav_menu_attr_title","YB_nav_menu_attr_title");
function YB_nav_menu_attr_title() {echo "\n<!-- ".__function__." -->\n";}

add_action("nav_menu_item_title","YB_nav_menu_item_title");
function YB_nav_menu_item_title() {echo "\n<!-- ".__function__." -->\n";}

add_action("oembed_iframe_title_attribute","YB_oembed_iframe_title_attribute");
function YB_oembed_iframe_title_attribute() {echo "\n<!-- ".__function__." -->\n";}

add_action("post_search_columns","YB_post_search_columns");
function YB_post_search_columns() {echo "\n<!-- ".__function__." -->\n";}

add_action("post_type_archive_title","YB_post_type_archive_title");
function YB_post_type_archive_title() {echo "\n<!-- ".__function__." -->\n";}

add_action("pre_get_document_title","YB_pre_get_document_title");
function YB_pre_get_document_title() {echo "\n<!-- ".__function__." -->\n";}

add_action("privacy_on_link_title","YB_privacy_on_link_title");
function YB_privacy_on_link_title() {echo "\n<!-- ".__function__." -->\n";}

add_action("private_title_format","YB_private_title_format");
function YB_private_title_format() {echo "\n<!-- ".__function__." -->\n";}

add_action("protected_title_format","YB_protected_title_format");
function YB_protected_title_format() {echo "\n<!-- ".__function__." -->\n";}

add_action("retrieve_password_title","YB_retrieve_password_title");
function YB_retrieve_password_title() {echo "\n<!-- ".__function__." -->\n";}

add_action("sanitize_title","YB_sanitize_title");
function YB_sanitize_title() {echo "\n<!-- ".__function__." -->\n";}

add_action("single_cat_title","YB_single_cat_title");
function YB_single_cat_title() {echo "\n<!-- ".__function__." -->\n";}

add_action("single_post_title","YB_single_post_title");
function YB_single_post_title() {echo "\n<!-- ".__function__." -->\n";}

add_action("single_tag_title","YB_single_tag_title");
function YB_single_tag_title() {echo "\n<!-- ".__function__." -->\n";}

add_action("single_term_title","YB_single_term_title");
function YB_single_term_title() {echo "\n<!-- ".__function__." -->\n";}

add_action("site_health_navigation_tabs","YB_site_health_navigation_tabs");
function YB_site_health_navigation_tabs() {echo "\n<!-- ".__function__." -->\n";}

add_action("the_title","YB_the_title");
function YB_the_title() {echo "\n<!-- ".__function__." -->\n";}

add_action("the_title_export","YB_the_title_export");
function YB_the_title_export() {echo "\n<!-- ".__function__." -->\n";}

//add_action("the_title_rss","YB_the_title_rss");
//function YB_the_title_rss() {echo "\n<!-- ".__function__." -->\n";}

add_action("walker_nav_menu_start_el","YB_walker_nav_menu_start_el");
function YB_walker_nav_menu_start_el() {echo "\n<!-- ".__function__." -->\n";}

add_action("widget_title","YB_widget_title");
function YB_widget_title() {echo "\n<!-- ".__function__." -->\n";}

add_action("wp_insert_post_empty_content","YB_wp_insert_post_empty_content");
function YB_wp_insert_post_empty_content() {echo "\n<!-- ".__function__." -->\n";}

add_action("wp_post_revision_title_expanded","YB_wp_post_revision_title_expanded");
function YB_wp_post_revision_title_expanded() {echo "\n<!-- ".__function__." -->\n";}

//add_action("wp_title_parts","YB_wp_title_parts");
//function YB_wp_title_parts() {echo "\n<!-- ".__function__." -->\n";}

//add_action("wp_title_rss","YB_wp_title_rss");
//function YB_wp_title_rss() {echo "\n<!-- ".__function__." -->\n";}

*/
