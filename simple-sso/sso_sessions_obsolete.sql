-- 1. Shared table
-- Put this in a small database both apps can reach
-- (a dedicated sso_shared DB is cleanest — don't bury it inside WP's or PW's own schema).
CREATE TABLE sso_sessions (
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
