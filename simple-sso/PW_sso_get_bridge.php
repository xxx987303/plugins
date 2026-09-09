<?php namespace ProcessWire;
/**
 * 4. ProcessWire side (e.g. site/init.php or a small autoload module)
 */

require_once __dir__ . '/SSOBridge.php';

function sso_get_bridge(): \SSOBridge {
    global $config;
    static $bridge = null;

    if ($bridge === null) {
	try {
        $pdo = new \PDO( 'mysql:host=localhost;dbname=yb_sso;charset=utf8mb4',
	    $config->dbUser, $config->dbPass,
            [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
        );
	} catch (PDOException $e) {
	    echo "FAILED: " . $e->getMessage();
	}
        $bridge = new \SSOBridge($pdo, SSO_DOMAIN);
    }
    return $bridge;
}
/*
// A. On successful PW login, register the shared session
$wire->addHookAfter('Session::login', function (HookEvent $event) {
    $user = $event->return;
    if ($user && $user->id) {
        try {
            sso_get_bridge()->createSession($user->email);
        } catch (\Throwable $e) {
            error_log('[SSO] createSession failed: ' . $e->getMessage());
            // swallow it — local login must still succeed
        }
    }
});

// B. On every load, if not locally logged in, check for a valid SSO session
if (!$wire->user->isLoggedin()) {
    debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
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
 */
