<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

function WD_register_admin_page() {
    wd_log();
    $wcd = getcwd();
    add_menu_page(
        'Visitor Counter',
        'Visitor Counter',
        'manage_options',
        'visitor-counter',
        'WD_display_admin_page',
        'dashicons-chart-bar',
        20
    );
}
add_action('admin_menu', 'WD_register_admin_page');

function WD_display_admin_page() {
    var_dump($_SERVER['REQUEST_URI']); exit;
    wd_log();
    
    $total_visits = wddb->get_var("SELECT COUNT(*) FROM ".WDstats);
    $user_visits  = wddb->get_results("SELECT user_id, COUNT(*) as visits FROM ".WDstats." WHERE user_id IS NOT NULL GROUP BY user_id");
?>    
<div class="wrap">
 <h1>Visitor Statistics</h1>
 <p>Total Visits: <?php echo $total_visits;?></p>
    
 <h2>Authenticated User Visits</h2>
   <table class="widefat">
   <thead><th>User ID</th><th>Visits</th></thead>
   <tbody>
<?php    
    foreach ($user_visits as $user_visit) {
      echo ('<tr>' .
	    '<td>' . $user_visit->user_id . '</td>'.
	    '<td>' . $user_visit->visits . '</td>'.
	    '</tr>');
    }
?>    
    </tbody>
  </table>
</div>
<?php } ?>
