#! /bin/bash
#
# Main script
#
# Extract information from cPanel logs.
# Unfortunately user_name is not available...
#

set -e
[ -n "$1" ] && exit 1;

tp=yb.onestudio.ch-ssl_log-???-20??
echo "Processing $tp"
sleep 1

cd ~/github/plugins.git/simple-watch-dog/logs
rm -f $tp
open -g -W $tp.gz
ls -l $tp
sleep 1
for f in $(ls -1 $tp); do
    # f=$(echo $gz|sed s/.gz$//)
    # [ -f $f ] || { open -g -W $gz; sleep 5; }
    tmp=/tmp/$f
    grep -E "/(adb|restor)/[a-z0-9]*/ " $f > $tmp || echo -n
    x=$(basename $0)
    script=$(echo $x|sed s/sh$/php/)
    php $script $tmp || echo -n
done
rm -fv $tp | wc
