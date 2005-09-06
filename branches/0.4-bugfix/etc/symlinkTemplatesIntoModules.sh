#!/bin/bash

REPO_ROOT=/var/www/html/seagull/branches/0.4-bugfix

# get list of installed modules
moduleList=`ls modules`;

for moduleName in $moduleList;
do
    ln -s $REPO_ROOT/www/themes/default/$moduleName $REPO_ROOT/modules/$moduleName/templates
done;