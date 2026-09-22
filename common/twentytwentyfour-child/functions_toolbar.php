<?php
/**
 * Customize the WordPress Admin Bar / Toolbar
 */

if (!defined('SH')) define('SH', '/sh_imac/');

// Force toolbar to be shown
add_filter('show_admin_bar', '__return_true');

if (false) echo "
<style>
#wpadminbar li[class*='has-site-icon'] {
    /* Your custom CSS rules here */
}
</style>
";

/**
 * REMOVE ITEMS FROM THE TOOLBAR
 */
function YB_toolbar_remove_items() {
    global $wp_admin_bar;
    WD_message('entry');

    // Remove the WordPress Logo
    $wp_admin_bar->remove_node('wp-logo');    WD_message('wp-logo');
    
    // Remove the Site Name / View Site link
    $wp_admin_bar->remove_node('site-name');  WD_message('site-name');
    
    // Remove the Updates icon
    $wp_admin_bar->remove_node('updates');    WD_message('updates');
    
    // Remove the Comments icon
    $wp_admin_bar->remove_node('comments');   WD_message('comments');
    
    // Remove the "+ New" content menu
    $wp_admin_bar->remove_node('new-content');WD_message('new-comment');
    
    // Remove the User Profile / "Howdy" menu (Far Right)
    // $wp_admin_bar->remove_node('my-account');WD_message('my-account');
    WD_message('exit');
}
add_action('wp_before_admin_bar_render', 'YB_toolbar_remove_items', 999);

/**
 * ADD A CUSTOM ROOT ITEM
 */
function YB_toolbar_add_item() {
    global $wp_admin_bar;
    $logo = SH.'site/assets/files/0000/SH_logo_circle_60.jpg'; 
    $args = ['id'    => 'sh_link',                           // Unique ID for the item        
             //'title' => 'Sweet Home',                        // Text displayed in the bar
             'title' => "<img src='$logo' width=28 height=28 style='vertical-align:middle; margin-right:8px; border-radius:50%;'/>Sweet Home\n",
             'href'  => SH,                                  // URL target
             'meta'  => [//'class'  => 'sh-class',           // Class
			 'target' => '_blank',               // Opens in a new tab
			 'title'  => 'Click here for help']];// Hover tooltip
    WD_message($args);
    $wp_admin_bar->add_node($args);
}
add_action('admin_bar_menu', 'YB_toolbar_add_item', 16);

/**
 * ADD A SUB-ITEM TO AN EXISTING MENU
 */
function YB_toolbar_add_sub_item() {
    global $wp_admin_bar;

    $args = ['id'     => 'YB_sub_post_type',
             'title'  => 'New Special Product',
             'parent' => 'new-content',
             'href'   => admin_url('post-new.php?post_type=product')];
    WD_message($args);
    $wp_admin_bar->add_node($args);
}
//add_action('admin_bar_menu', 'YB_toolbar_add_sub_item', 999);

/**
 * Add item with title and image
 */
function YB_toolbar_add_item_image( $wp_admin_bar ) {

    $logo = SH.'site/assets/files/0000/SH_logo_circle_60.jpg'; 
    
    //$html_title = '<img src="' . esc_url( $logo ) . '" style="width:20px; height:20px; vertical-align:middle; margin-right:8px; border-radius:50%;"/>'.
    $args = ['id'    => 'sh_link',        
             'title' => "<img src='$logo' width=28 height=28 style='vertical-align:middle; margin-right:8px; border-radius:50%;'/>Sweet Home\n",
             'href'  => SH,
             'meta'  => ['class'           => 'has-site-icon',
			 //'container_class' => 'has-site-icon',
			 'title'           => 'Visit the main page']];
    WD_message($args);
    $wp_admin_bar->add_node( $args );
}
add_action( 'admin_bar_menu', 'YB_toolbar_add_item_image', 9 );

function custom_toolbar_with_image( $wp_admin_bar ) {

    $image_url = SH.'site/assets/files/0000/SH_logo_circle_60.jpg';

    $html_title  = '<img src="' . esc_url( $image_url ) . '" style="width:30px; height:30px; vertical-align:middle; margin-right:8px; border-radius:50%;"/>';
    $html_title .= 'Sweet home';
    //$html_title .= '<span class="ab-label">My Brand</span>';
    
    $args = ['meta'  => ['title' => 'Visit our main page'], // Tooltip message
	     'id'    => 'my_custom_image_node', 
             'title' => $html_title,             // HTML containing your image and text
             'href'  => SH ];
    WD_message($args);
    $wp_admin_bar->add_node( $args );
}
add_action( 'admin_bar_menu', 'custom_toolbar_with_image', 999 );
