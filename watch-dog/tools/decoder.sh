#! /bin/bash

HOME=restor

cd /Users/yb/Sites/$HOME/wp-content/plugins/wp-watch-dog

php decode_log.php|grep -vE "'/$HOME/?$'"|grep /$HOME|grep -vE "uri=.'?/'?(wp-|fav|_| [a-z]|cgi|cPanel|$HOME/(xmlrpc|sapp|wp-)|[A_Z'\?]+)"
#php decode_log.php|grep -vE "/wp-|/(feed|$HOME)/? *?$"
#|awk '{print $2" "$1" "$3" "$4}'
