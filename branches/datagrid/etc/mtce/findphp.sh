#!/bin/bash

find docs lib modules tests www \
    -name _darcs -prune -o \
    \( -type f \
    -a -name \*.php \
    -a ! -name .\* \
    -a ! -name \*8859\* \
    -a ! -name \*big5\* \
    -a ! -name \*gb2312\* \
    -a ! -name \*utf-8\* \
    -a ! -name \*windows-1251\* \
    \)
