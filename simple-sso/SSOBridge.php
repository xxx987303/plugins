<?php
/**
 * 2. Shared PHP class (SSOBridge.php)
 * Both CMSs require this one file. It knows nothing about WP or PW — just tokens and cookies.
 */
// SSOBridge.php

define('TS', 'Y-m-d H:i:s');
if (!defined('COOKIE_DOMAIN')) define('COOKIE_DOMAIN', @$_SERVER['HTTP_HOST']);
if (!defined('COOKIE_NAME'))   define('COOKIE_NAME', 'SSOSESSID');
if (!defined('LOCALHOSTs'))    define('LOCALHOSTs', ['127.0.0.1', '::1', 'localhost']);

/**
 */
class SSOBridge {
    private PDO $db;
    private int $ttl = 60 * 60 * 24 * 14; // 14 days

    public function __construct(PDO $db, string $cookieDomain=COOKIE_DOMAIN) {
	WD_message('entry');
        $this->db = $db;
	$this->initDB();
	WD_message('exit');
    }

    /**
     * Create table(s) if not yet done
     */
    public function initDB() {
	$sql = "CREATE DATABASE IF NOT EXISTS `yb_sso`;
                CREATE TABLE    IF NOT EXISTS `yb_sso`.`sso_sessions` (
                 token         CHAR(64)      PRIMARY KEY,
                 user_email    VARCHAR(255)  NOT NULL,
                 created_at    datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
                 expires_at    datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
                 last_seen_at  datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
                 ip            VARCHAR(45),
                 user_agent    VARCHAR(255),
                 KEY idx_email (user_email),
                 KEY idx_expires (expires_at)
                 ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
	";
	$this->db->query($sql);
    }
    
    /**
     * Call right after a successful local login on either site.
     */
    public function createSession(string $email): string {
	WD_message('entry');
        $token = bin2hex(random_bytes(32));
        $now = time();

        $stmt = $this->db->prepare(
            "INSERT INTO sso_sessions (token, user_email, created_at, expires_at, last_seen_at, ip, user_agent) ".
            "VALUES (:token, :email, :now, :exp, :now, :ip, :ua)");
        $stmt->execute($args=[':token' => $token,
			      ':email' => $email,
			      ':now'   => date(TS,$now),
			      ':exp'   => date(TS,$now + $this->ttl),
			      ':ip'    => in_array(($ip=$_SERVER['REMOTE_ADDR']), LOCALHOSTs) ? '127.0.0.1' : $ip,
			      ':ua'    => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255)]);
	WD_message('INSERT '.joinX($args),'blue');

	// Create Cookie
        setcookie(COOKIE_NAME, $token, ($c=['expires'  => $now + $this->ttl,
					    'path'     => '/',
					    'domain'   => COOKIE_DOMAIN,
					    'secure'   => true,
					    'httponly' => true,
					    'samesite' => 'Lax']));
	WD_message("Create cookie token ".joinX($c));
	WD_message('exit');
        return $token;
    }

    /**
     * Call on every page load. Returns the logged-in email, or null.
     */
    public function getSessionEmail(): ?string {
	WD_message('entry');
        $token = $_COOKIE[COOKIE_NAME] ?? null;
        if (!$token || !preg_match('/^[a-f0-9]{64}$/', $token)) {
	    WD_message("No token found");
	    WD_message('exit');
            return null;
        }

        $stmt = $this->db->prepare(
            "SELECT user_email, expires_at FROM sso_sessions WHERE token = :token"
        );
        $stmt->execute([':token' => $token]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row || strtotime($row['expires_at']) < time()) {
            $this->destroySession(); // stale/invalid cookie, clean it up
	    WD_message('exit');
            return null;
        }

        // sliding expiry — keep active users logged in
        $this->db->prepare("UPDATE sso_sessions SET last_seen_at = :now WHERE token = :token")
                 ->execute([':now' => date(TS,time()), ':token' => $token]);

	WD_message($row['user_email']);
	WD_message('exit');
        return $row['user_email'];
    }

    /**
     * Call on logout from either site.
     */
    public function destroySession(): void {
	WD_message('entry');
        $token = $_COOKIE[COOKIE_NAME] ?? null;
        if ($token) {
	    $this->db->prepare($sql="DELETE FROM sso_sessions WHERE token = :token")->execute([':token' => $token]);
	    WD_message($sql,'blue');
	}

        setcookie(COOKIE_NAME, '', ['expires'  => time() - 3600,            
				    'path'     => '/',
				    'domain'   => COOKIE_DOMAIN,
				    'secure'   => true,
				    'httponly' => true,
				    'samesite' => 'Lax']);
	WD_message('exit');
    }
}
