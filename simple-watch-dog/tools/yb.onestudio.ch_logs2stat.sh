#! /bin/bash
#
# Extract information from cPanel logs.
# Unfortunately user_name is not available...
#

set -e
t='/tmp/yb.onestudio.ch_log'

tp=yb.onestudio.ch-ssl_log-???-202?
for gz in $(ls -1 ~/Downloads/$tp.gz); do
    f=$(echo $gz|sed s/.gz$//)
    [ -f $f ] || { open $gz; sleep 5; }
    echo $f
    grep -E "/(adb|restor)/[a-z0-9]*/ " $f > $t
    x=$(basename $0)
    script=$(echo $x|sed s/sh$/php/)
    php $script $t
    rm -v $f
done


