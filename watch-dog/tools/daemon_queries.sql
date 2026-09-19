--
-- Queries from study how to replace wd_visits...
--

-- Attempt to find a missed user fro Russia
SELECT d_time,d_uri,r_user_ID,d_remote,r_country  FROM wd_daemon LEFT JOIN wd_remotes ON d_remote=r_remote WHERE d_user_agent='Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36' ORDER BY d_time;

-- Merge wd_daemon and wd_visits
SELECT TIMESTAMPDIFF(SECOND,d_time,v.time) AS d,d_time,v.user_id,d_remote,r_country,d_uri FROM wd_daemon LEFT JOIN wd_remotes ON d_remote=r_remote LEFT JOIN wd_visits AS v ON d_remote=remote AND d_user_agent=user_agent AND uri=d_uri HAVING d<10 AND d>=0 ORDER BY d_time;

-- Looking for crawlers
SELECT COUNT(*) as c,d_time,d_remote,d_user_agent FROM wd_daemon LEFT JOIN wd_remotes ON d_remote=r_remote GROUP BY CONCAT_WS('|',d_time,d_remote,d_user_agent) HAVING c>1 ORDER BY d_remote;

SELECT COUNT(*) as c,d_time,d_uri,d_remote,r_user_id AS ID,r_country FROM wd_daemon LEFT JOIN wd_remotes ON d_remote=r_remote WHERE d_time IS NOT NULL GROUP BY d_time ORDER BY d_time;              

SELECT COUNT(*) as c,d_time,d_uri,d_remote,r_user_id AS u,r_country,d_user_agent  FROM wd_daemon LEFT JOIN wd_remotes ON d_remote=r_remote WHERE r_user_id IS NULL AND d_time IS NOT NULL GROUP BY d_time ORDER BY d_time;

SELECT d_time,d_remote,r_country,r_user_id,d_uri  FROM wd_daemon LEFT JOIN wd_remotes ON d_remote=r_remote WHERE r_user_id!=1 ORDER BY CONCAT_WS('|',d_remote,d_user_agent);
