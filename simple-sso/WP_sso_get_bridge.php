<?php
/* Plugin Name: SSO Bridge */

require_once __dir__ . '/SSOBridge.php';

function sso_get_bridge(): SSOBridge {
    static $bridge = null;
    if ($bridge === null) {
        $pdo = new PDO(
            'mysql:host='.DB_HOST.';dbname=yb_sso;charset=utf8mb4',
            DB_USER, DB_PASSWORD,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        $bridge = new SSOBridge($pdo, SSO_DOMAIN);
    }
    return $bridge;
}

// A. On successful WP login, register the shared session
add_action('wp_login', function ($user_login, $user) {
    sso_get_bridge()->createSession($user->user_email);
}, 10, 2);

// B. On every load, if not locally logged in, check for a valid SSO session
add_action('init', function () {
    if (is_user_logged_in()) return;

    $email = sso_get_bridge()->getSessionEmail();
    if (!$email) return;

    $user = get_user_by('email', $email);
    if (!$user) return; // policy choice: bail, or auto-provision here

    wp_set_current_user($user->ID);
    wp_set_auth_cookie($user->ID);
});

// C. On WP logout, kill the shared session too
add_action('wp_logout', function () {
    sso_get_bridge()->destroySession();
});
