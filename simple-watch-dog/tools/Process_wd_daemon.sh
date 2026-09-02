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

cd ~/github/plugins.git/simple-watch-dog/logs

# Unpack logfiles
#rm -f $tp;
#open -g -W $tp.gz

# Get the php script
x=$(basename $0)
script=$(echo $x|sed s/sh$/php/)

# Execute it
php $script "$tp" || echo -n

#rm -fv $tp | wc
