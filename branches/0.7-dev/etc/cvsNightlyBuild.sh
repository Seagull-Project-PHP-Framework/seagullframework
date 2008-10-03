#!/bin/bash

export CVSROOT=:pserver:anonymous:seagull@phpkitchen.com:/var/cvs;

echo "login to cvs...";

cvs login;

echo "Get latest SGL from cvs";

# get sgl

cvs get seagull;

# make .tar.gz archive

tar -cz -f seagull.tar.gz seagull;

echo "finished... Have fun!";
