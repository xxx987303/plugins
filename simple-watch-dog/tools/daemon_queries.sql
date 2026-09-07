SELECT d_time,d_uri,d_remote,r_user_id AS ID,r_country FROM wd_daemon LEFT JOIN wd_remotes ON d_remote=r_remote WHERE d_time IS NOT NULL GROUP BY d_time ORDER BY d_time;              

SELECT d_time,d_uri,d_remote,r_user_id AS ,r_country,d_user_agent  FROM wd_daemon LEFT JOIN wd_remotes ON d_remote=r_remote WHERE r_user_id IS NULL AND d_time IS NOT NULL GROUP BY d_time ORDER BY d_time;

SELECT d_time,d_remote,r_country,r_user_id,d_uri  FROM wd_daemon LEFT JOIN wd_remotes ON d_remote=r_remote WHERE r_user_id!=1 ORDER BY CONCAT_WS('|',d_remote,d_user_agent);
