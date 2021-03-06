#!/bin/bash
#
# Look in the local directory for PHP files that have tabs in them. If
# there are files with tabs, print the list of files and exit with
# non-zero status.

tabs=$(./etc/mtce/findphp.sh | xargs egrep -n '	' | sort)

if [ ! -z "$tabs" ]
    then
    cat <<EOF 1>&2
Found tabs in:
$tabs
EOF
    exit 1
fi
