#!/bin/bash

REPO_ROOT=/var/www/html/seagull/trunk

# get list of installed modules
moduleList=`ls $REPO_ROOT/modules`;

for moduleName in $moduleList;
do
    ln -s $REPO_ROOT/www/themes/default/$moduleName $REPO_ROOT/modules/$moduleName/templates
done;
