<?php

/**
 * The new page template is an empty page
 */
if (0) add_filter('template_include', function($template) {
    if (is_page()) {
        //echo"<pre>";debug_print_backtrace(); die("template=$template\n");
        return get_stylesheet_directory() . '/page-empty.php';
    }
    return $template;
});

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

