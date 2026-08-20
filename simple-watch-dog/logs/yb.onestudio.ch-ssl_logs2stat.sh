#! /bin/bash
#
# Main script
#
# Extract information from cPanel logs.
# Unfortunately user_name is not available...
#

set -e

tp=yb.onestudio.ch-ssl_log-???-2026
t='/tmp/yb.onestudio.ch_log'

for gz in $(ls -1 $tp.gz); do
    f=$(echo $gz|sed s/.gz$//)
    [ -f $f ] || { open $gz; sleep 5; }
    echo $f
    grep -E "/(adb|restor)/[a-z0-9]*/ " $f > $t
    x=$(basename $0)
    script=$(echo $x|sed s/sh$/php/)
    php $script $t || echo -n
    rm -v $f
done


