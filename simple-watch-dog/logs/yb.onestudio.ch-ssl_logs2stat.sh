#! /bin/bash
#
# Main script
#
# Extract information from cPanel logs.
# Unfortunately user_name is not available...
#

set -e

tp=yb.onestudio.ch-ssl_log-???-20??

cd ~/github/plugins.git/simple-watch-dog/logs
for gz in $(ls -1 $tp.gz); do
    f=$(echo $gz|sed s/.gz$//)
    [ -f $f ] || { open -g -W $gz; }
    ls -l $f
    tmp=/tmp/$f
    grep -E "/(adb|restor)/[a-z0-9]*/ " $f > $tmp || echo -n
    x=$(basename $0)
    script=$(echo $x|sed s/sh$/php/)
    php $script $tmp || echo -n
    rm $f
done


