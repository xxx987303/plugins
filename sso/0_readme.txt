=== Simple SSO ===
Contributors: Claude and YB
Tags: Session based login
Requires at least: 5.0
Tested up to: 7.0.4
Stable tag: 2.0
License: GPL3

Shared-session plugin for one domain and several CMS sites. Currently WordPress & ProcessWire

== Changelog ==

   = 2.0 =
     Change from INT to DATETIME the timestamps in the database

   = 1.0 =
     Initial version from Claude


== notes from Claude ==

## Things to decide before you build this for real:

- **Auto-provisioning:** if someone exists in WP but not in PW (or vice versa), do you
  create them on the fly, or require accounts to exist on both sides first?
  The sketch above just bails silently — you'll want at least a log entry.

- **Instant cross-site logout:** logging out on Site A deletes the shared row immediately,
  but Site B only notices on its *next* page load — until then its local session cookie
  still looks valid. Usually fine (worst case: a few seconds/minutes of staleness).
  If you need it instant, Site A's logout handler can fire a quick internal request to a small
  "clear local session" endpoint on Site B.

- **Password changes / account disable:** decide whether those should also revoke rows in
  `sso_sessions` for that email, so a disabled user isn't still riding an old shared session.

- **Secrets:** don't hardcode the DB password — pull from environment variables or each
  CMS's existing config file, as shown with `getenv()`.

- **Table cleanup:** a daily cron `DELETE FROM sso_sessions WHERE expires_at < UNIX_TIMESTAMP()`
  keeps the table from growing forever.
  This is deliberately minimal — no admin UI, no refresh tokens, no protocol.
  If later you add a site on a different domain, that one site becomes an OIDC client
  against a proper IdP while these two keep using the shared session between themselves.
