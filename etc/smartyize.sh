#!/bin/sh

if [ $# -ne 1 ]; then
    echo 1>&2 Usage: $0 file
    exit 12
fi

file=$1

sr.pl "#" "'" $file
sr.pl pageTitle "\$result->pageTitle" $file
sr.pl "{" "{\$result->" $file

exit 0
