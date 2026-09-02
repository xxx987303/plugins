#!/usr/bin/env bash
#
# awstats_parser.sh
#
# Fast, dependency-free extraction of top-line stats from a cPanel/AWStats
# raw data file, e.g.:
#   /home/<user>/tmp/awstats/awstats<MM><YYYY>.<domain>.txt
#
# Pulls the BEGIN_GENERAL block (key/value counters) and can also dump
# any single record-style section as TSV using awk, without needing PHP.
#
# USAGE
#   ./awstats_parser.sh <file>                  # print GENERAL key/values
#   ./awstats_parser.sh <file> --section=SIDER  # dump raw section as TSV
#   ./awstats_parser.sh <file> --sections       # list section names + counts
#
set -euo pipefail

FILE="${1:-}"
if [[ -z "$FILE" || ! -r "$FILE" ]]; then
    echo "Usage: $0 <awstats_raw_file> [--section=NAME] [--sections]" >&2
    exit 1
fi
shift || true

MODE="general"
SECTION=""

for arg in "$@"; do
    case "$arg" in
        --sections) MODE="sections" ;;
        --section=*) MODE="section"; SECTION="${arg#--section=}" ;;
        *) ;;
    esac
done

case "$MODE" in
    general)
        # Print everything between BEGIN_GENERAL and END_GENERAL as key<TAB>value
        awk '
            /^BEGIN_GENERAL/ { inblock=1; next }
            /^END_GENERAL/   { inblock=0; next }
            inblock {
                key=$1
                $1=""
                sub(/^ /,"")
                printf "%-25s %s\n", key, $0
            }
        ' "$FILE"
        ;;

    sections)
        # List every BEGIN_<SECTION> <count> line found in the file
        grep -E '^BEGIN_[A-Z0-9_]+' "$FILE" | \
            awk '{ name=$1; sub(/^BEGIN_/,"",name); count=($2=="" ? "-" : $2); printf "%-20s %s\n", name, count }'
        ;;

    section)
        if [[ -z "$SECTION" ]]; then
            echo "Provide --section=NAME (see --sections for available names)" >&2
            exit 1
        fi
        awk -v want="BEGIN_${SECTION}" -v endwant="END_${SECTION}" '
            $0 ~ "^"want { inblock=1; next }
            $0 ~ "^"endwant { inblock=0; next }
            inblock { print }
        ' "$FILE" | tr -s ' ' '\t'
        ;;
esac