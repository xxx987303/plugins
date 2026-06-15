#! /bin/bash

cd /Users/yb/Sites/restor/wp-content/plugins/wp-watch-dog

php decode_log.php|grep -vE "'/restor/?$'"|grep /restor|grep -vE "uri=.'?/'?(wp-|fav|_| [a-z]|cgi|cPanel|restor/(xmlrpc|sapp|wp-)|[A_Z'\?]+)"
#php decode_log.php|grep -vE "/wp-|/(feed|restor)/? *?$"
#|awk '{print $2" "$1" "$3" "$4}'
