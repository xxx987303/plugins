<?php

/**
 */
function wd_enqueue_visit_duration_script() {
    wp_enqueue_script('visit-duration', plugin_dir_url(__file__) . 'js/visit-duration.js', [], null, true);
    wp_localize_script('visit-duration', 'ajaxurl', admin_url('admin-ajax.php'));
}
add_action('wp_enqueue_scripts', 'wd_enqueue_visit_duration_script');

/**
 */
  function track_visit_duration() {
    var_dump($_POST['duration']);    die(9999);
    if (isset($_POST['duration'])) {
        $duration = intval($_POST['duration']);
        $_POST['duration'] = $duration;

        $post_id = get_the_ID();
        
        // Save the duration data. This is just an example of saving it as a post meta.
        add_post_meta($post_id, 'visit_duration', $duration);

        // You might want to save it differently depending on your needs.
        // For example, you could save it in a custom table or log it somewhere.

        wp_send_json_success();
    } else {
        wp_send_json_error('No duration provided');
    }
}
add_action('wp_ajax_track_visit_duration',        'track_visit_duration');
add_action('wp_ajax_nopriv_track_visit_duration', 'track_visit_duration');
/*
function track_visit_duration() {
    if (isset($_POST['duration'])) {
        $duration = intval($_POST['duration']);
        $post_id = get_the_ID();

        // Save the duration data. This is just an example of saving it as a post meta.
        add_post_meta($post_id, 'visit_duration', $duration);

        // You might want to save it differently depending on your needs.
        // For example, you could save it in a custom table or log it somewhere.

        wp_send_json_success();
    } else {
        wp_send_json_error('No duration provided');
    }
}
add_action('wp_ajax_track_visit_duration', 'track_visit_duration');
add_action('wp_ajax_nopriv_track_visit_duration', 'track_visit_duration');
*/
