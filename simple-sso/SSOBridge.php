<?php
/**
 * 2. Shared PHP class (SSOBridge.php)
 * Both CMSs require this one file. It knows nothing about WP or PW — just tokens and cookies.
 */
// SSOBridge.php

define('SSO_DOMAIN', $_SERVER['HTTP_HOST']);

/**
 */
class SSOBridge {
    private PDO $db;
    private string $cookieName = 'SSOSESSID';
    private string $cookieDomain;
    private int $ttl = 60 * 60 * 24 * 14; // 14 days

    public function __construct(PDO $db, string $cookieDomain) {
        $this->db = $db;
        $this->cookieDomain = $cookieDomain;
	$this->initDB();
    }

    /** Create table(s) if not yet done */
    public function initDB() {
	$sql = "CREATE DATABASE IF NOT EXISTS `sso_shared`;
                CREATE TABLE    IF NOT EXISTS `sso_shared`.`sso_sessions` (
                 token         CHAR(64)      PRIMARY KEY,
                 user_email    VARCHAR(255)  NOT NULL,
                 created_at    INT UNSIGNED  NOT NULL,
                 expires_at    INT UNSIGNED  NOT NULL,
                 last_seen_at  INT UNSIGNED  NOT NULL,
                 ip            VARCHAR(45),
                 user_agent    VARCHAR(255),
                 KEY idx_email (user_email),
                 KEY idx_expires (expires_at)
                 ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
	";
	$this->db->query($sql);
    }
    
    /** Call right after a successful local login on either site. */
    public function createSession(string $email): string {
        $token = bin2hex(random_bytes(32));
        $now = time();

        $stmt = $this->db->prepare(
            "INSERT INTO sso_sessions
                (token, user_email, created_at, expires_at, last_seen_at, ip, user_agent)
             VALUES (:token, :email, :now, :exp, :now, :ip, :ua)"
        );
        $stmt->execute([
            ':token' => $token,
            ':email' => $email,
            ':now'   => $now,
            ':exp'   => $now + $this->ttl,
            ':ip'    => $_SERVER['REMOTE_ADDR'] ?? '',
            ':ua'    => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
        ]);

        setcookie($this->cookieName, $token, [
            'expires'  => $now + $this->ttl,
            'path'     => '/',
            'domain'   => $this->cookieDomain, // e.g. SSO_DOMAIN
            'secure'   => true,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        return $token;
    }

    /** Call on every page load. Returns the logged-in email, or null. */
    public function getSessionEmail(): ?string {
        $token = $_COOKIE[$this->cookieName] ?? null;
        if (!$token || !preg_match('/^[a-f0-9]{64}$/', $token)) {
            return null;
        }

        $stmt = $this->db->prepare(
            "SELECT user_email, expires_at FROM sso_sessions WHERE token = :token"
        );
        $stmt->execute([':token' => $token]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row || $row['expires_at'] < time()) {
            $this->destroySession(); // stale/invalid cookie, clean it up
            return null;
        }

        // sliding expiry — keep active users logged in
        $this->db->prepare("UPDATE sso_sessions SET last_seen_at = :now WHERE token = :token")
                 ->execute([':now' => time(), ':token' => $token]);

        return $row['user_email'];
    }

    /** Call on logout from either site. */
    public function destroySession(): void {
        $token = $_COOKIE[$this->cookieName] ?? null;
        if ($token) $this->db->prepare("DELETE FROM sso_sessions WHERE token = :token")->execute([':token' => $token]);
	
        setcookie($this->cookieName, '', [
            'expires'  => time() - 3600,
            'path'     => '/',
            'domain'   => $this->cookieDomain,
            'secure'   => true,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }
}
