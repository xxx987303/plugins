#! /bin/bash
#
# This script creates `yb-watch-dog`.`wd_visits` table with data from OneStudio
# -----------------------------------------------------------------------------
#

set -x
set -e

database="yb-watch-dog"

# Run script restor_one_M2:
# ~/bin/restor_one_M2.sh

echo "#"
echo "# Extracting table wp_wd_visitor_stats, restoring the yb.onestudio.ch home name"
echo "#"
set -x
mysqldump yb_restor wp_wd_visitor_stats > wp_wd_visitor_stats.sql
cat wp_wd_visitor_stats.sql | \
    grep -vE 'PRIMARY KEY' | \
    sed -e 's,wp_wd_visitor_stats,wd_visits,g' \
	-e    's,wd_visitor_stats,wd_visits,g' \
	-e 's,yb_restor,restor,g' \
	-e 's,AUTO_INCREMENT=[0-9]*,,' \
	-e 's,AUTO_INCREMENT,DEFAULT -1,' \
	-e 's:varchar(16) DEFAULT NULL,:varchar(16) DEFAULT NULL:' \
	> wd_visits.sql

sdiff -sbB -w200 wp_wd_visitor_stats.sql wd_visits.sql | head -22

mysql < wd_visits.sql $database

echo "#"
echo "# Replace the wd_visits table"
echo "#"
set -x

#DELETE FROM wd_visits WHERE user_name is null OR user_agent is null;
cat >t.sql <<EOF
DELETE FROM wd_visits WHERE user_id=0;
DELETE FROM wd_visits WHERE uri REGEXP 'wp-content';
DELETE FROM wd_visits WHERE remote = '127.0.0.1';
EOF
mysql < t.sql $database

# Initialise wd_remotes table with the imported sample
php  ../logs/Process_wd_daemon.php remotes
