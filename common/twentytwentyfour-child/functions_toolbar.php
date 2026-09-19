<?php
/**
 * Customize the WordPress Admin Bar / Toolbar
 */


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
 
    // Remove the WordPress Logo
    $wp_admin_bar->remove_node('wp-logo');
    
    // Remove the Site Name / View Site link
    $wp_admin_bar->remove_node('site-name');
    
    // Remove the Updates icon
    $wp_admin_bar->remove_node('updates');
    
    // Remove the Comments icon
    $wp_admin_bar->remove_node('comments');
    
    // Remove the "+ New" content menu
    $wp_admin_bar->remove_node('new-content');
    
    // Remove the User Profile / "Howdy" menu (Far Right)
    // $wp_admin_bar->remove_node('my-account');
}
add_action('wp_before_admin_bar_render', 'YB_toolbar_remove_items', 999);

/**
 * ADD A CUSTOM ROOT ITEM
 */
function YB_toolbar_add_item() {
    global $wp_admin_bar;
    $args = ['id'    => 'sh_link',                           // Unique ID for the item        
             'title' => 'Sweet Home',                        // Text displayed in the bar
	   //'img'   => 'class="site-icon" src="/sh/site/assets/files/0000/sh_logo50.png" alt="" width="20" height="20"',
	     //'img'   => '/sh/site/assets/files/0000/sh_logo50.png',
             'href'  => '/sh_imac/',                         // URL target
             'meta'  => [//'class'  => 'sh-class',             // Class
			 'target' => '_blank',               // Opens in a new tab
			 'title'  => 'Click here for help']];// Hover tooltip
    WD_message($args);
    $wp_admin_bar->add_node($args);
}
//add_action('admin_bar_menu', 'YB_toolbar_add_item', 16);

/**
 * ADD A SUB-ITEM TO AN EXISTING MENU
 */
function YB_toolbar_add_sub_item() {
    global $wp_admin_bar;

    $args = ['id'     => 'YB_sub_post_type',
             'title'  => 'New Special Product',
             'parent' => 'new-content',             // Makes it a child of the "+ New" menu
             'href'   => admin_url('post-new.php?post_type=product')];
    WD_message($args);
    $wp_admin_bar->add_node($args);
}
//add_action('admin_bar_menu', 'YB_toolbar_add_sub_item', 999);

/**
 * Add item with title and image
 */
function YB_toolbar_add_item_image( $wp_admin_bar ) {

    $image_url = '/sh/site/assets/files/0000/sh_logo50.png'; 
    
    //$html_title = '<img src="' . esc_url( $image_url ) . '" style="width:20px; height:20px; vertical-align:middle; margin-right:8px; border-radius:50%;"/>'.
    $html_title = "<img src='$image_url' width=28 height=28 style='vertical-align:middle; margin-right:8px; border-radius:50%;'/>Sweet Home\n";
//		  "<span class='ab-label'>Sweet Home</span>\n";
		  

    $args = ['id'    => 'sh_link',        
             'title' => $html_title,
             'href'  => '/sh_imac/',
             'meta'  => ['class'           => 'has-site-icon',
			 //'container_class' => 'has-site-icon',
			 'title'           => 'Visit the main page']];
    WD_message($args);
    $wp_admin_bar->add_node( $args );
}
add_action( 'admin_bar_menu', 'YB_toolbar_add_item_image', 9 );
