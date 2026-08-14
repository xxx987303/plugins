#! /bin/bash
#

set +x
set -e

datebases="yb_restor adb_tmp restor_tmp"

# Run script restor_one_M2:
# ~/bin/restor_one_M2.sh

echo "#"
echo "# Extracting table wp_wd_visitor_stats, restoring the yb.onestudio.ch home name"
echo "#"
set -x
mysqldump yb_restor wp_wd_visitor_stats | sed 's,/yb_restor/,/restor/,' > wp_wd_visitor_stats.sql
for db in $datebases; mysql < wp_wd_visitor_stats.sql $db; done

echo "#"
echo "# Replace the wp_wd_visitor_stats table"
echo "#"
set -x

cat >t.sql <<EOF
DELETE FROM wp_wd_visitor_stats WHERE user_id=0;
DELETE FROM wp_wd_visitor_stats WHERE user_name is null OR user_agent is null;
DELETE FROM wp_wd_visitor_stats WHERE uri REGEXP 'wp-content';
DELETE FROM wp_wd_visitor_stats WHERE remote = '127.0.0.1';
EOF
cat t.sql
for db in $datebases; mysql < t.sql $db; done

