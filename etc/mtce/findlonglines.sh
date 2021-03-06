#!/bin/bash

files_with_long_lines=$(./etc/mtce/findphp.sh |
            xargs -l1 wc -L |
            awk '$1 > 80 { print $2 }' |
            xargs -l1 --replace=FILENAME /usr/bin/env perl etc/mtce/longlines.pl FILENAME 80)

if [ "$files_with_long_lines" ]
    then
    cat <<EOF 1>&2
Found lines > 80 characters in:

$files_with_long_lines
EOF
    exit 1
fi
