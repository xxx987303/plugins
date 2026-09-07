<?php namespace ProcessWire;
/**
 * 4. ProcessWire side (e.g. site/init.php or a small autoload module)
 */

require_once __DIR__ . '/../shared/sso-bridge.php';

function sso_get_bridge(): \SSOBridge {
    static $bridge = null;
    if ($bridge === null) {
        $pdo = new \PDO(
            'mysql:host=localhost;dbname=sso_shared;charset=utf8mb4',
            'ssouser', getenv('SSO_DB_PASS'),
            [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
        );
        $bridge = new \SSOBridge($pdo, '.example.com');
    }
    return $bridge;
}

// A. On successful PW login, register the shared session
$wire->addHookAfter('Session::login', function (HookEvent $event) {
    $user = $event->return; // the logged-in User, or false on failure
    if ($user && $user->id) {
        sso_get_bridge()->createSession($user->email);
    }
});

// B. On every load, if not locally logged in, check for a valid SSO session
if (!$wire->user->isLoggedin()) {
    $email = sso_get_bridge()->getSessionEmail();
    if ($email) {
        $pwUser = $wire->users->get("email=$email");
        if ($pwUser && $pwUser->id) {
            $wire->session->forceLogin($pwUser); // built-in PW method
        }
    }
}

// C. On PW logout, kill the shared session too
$wire->addHookBefore('Session::logout', function (HookEvent $event) {
    sso_get_bridge()->destroySession();
});
