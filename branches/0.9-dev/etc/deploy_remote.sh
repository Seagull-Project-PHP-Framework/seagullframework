#!/bin/bash

# +---------------------------------------------------------------------------+
# | script for deploying a Seagull release                                    |
# +---------------------------------------------------------------------------+

# args
REVISION_NUM=$1
PREVIOUS_REVISION_NUM=$(($REVISION_NUM - 1))
MODE_TEST=$2

USER=demian
DOMAIN=example.com
PROJECT_NAME=Seagull
UPLOADED_TARBALL=/tmp/$PROJECT_NAME-$REVISION_NUM.tar.gz
SEAGULL_DIR=/var/www/html/seagull/branches/0.6-bugfix


##############################
# check if this is a staging deployment
##############################
if [ -z $MODE_TEST ] ; then
    IS_LIVE=0;
else
    IS_LIVE=1;
fi


##############################
# dynamically set paths
##############################
if (( $IS_LIVE )) ; then
    DEPLOY_DIR=/var/www/html/sgl_deploy_live
    STATIC_DIR=/var/www/html/sgl_deploy_live/123
else
    DEPLOY_DIR=/var/www/html/sgl_deploy_staging
    STATIC_DIR=/var/www/html/sgl_deploy_staging/999
fi


##############################
# unzip tarball
##############################
cd /tmp
tar xvzf $UPLOADED_TARBALL
rm -f $UPLOADED_TARBALL


##############################
# check that we are not overwriting
##############################
if [ -d "$DEPLOY_DIR/$REVISION_NUM" ]; then
  echo "cannot overwrite existing deployment";
  exit 1;
fi


##############################
# move folder to deploy dir
##############################
mv $PROJECT_NAME-$REVISION_NUM $DEPLOY_DIR/$REVISION_NUM


##############################
# symlink images and var dirs
##############################
ln -s  $STATIC_DIR/var $DEPLOY_DIR/$REVISION_NUM/var
rm -rf $DEPLOY_DIR/$REVISION_NUM/www/images/Image
ln -s  $STATIC_DIR/www/images/Image $DEPLOY_DIR/$REVISION_NUM/www/images/Image


##############################
# make various dirs writable
##############################
chmod 777 $DEPLOY_DIR/$REVISION_NUM/www


##############################
# symlink in foo web resources
##############################
ln -s $DEPLOY_DIR/$REVISION_NUM/modulesFOO/foo/www $DEPLOY_DIR/$REVISION_NUM/www/foo


##############################
# symlink in Seagull repo
##############################
ln -s $SEAGULL_DIR/modules $DEPLOY_DIR/$REVISION_NUM/modules
ln -s $SEAGULL_DIR/modules/block $DEPLOY_DIR/$REVISION_NUM/modulesFOO/block
ln -s $SEAGULL_DIR/modules/default $DEPLOY_DIR/$REVISION_NUM/modulesFOO/default
ln -s $SEAGULL_DIR/modules/navigation $DEPLOY_DIR/$REVISION_NUM/modulesFOO/navigation
ln -s $SEAGULL_DIR/modules/translation $DEPLOY_DIR/$REVISION_NUM/modulesFOO/translation

ln -s $SEAGULL_DIR/lib/SGL.php $DEPLOY_DIR/$REVISION_NUM/lib/SGL.php
ln -s $SEAGULL_DIR/lib/SGL $DEPLOY_DIR/$REVISION_NUM/lib/SGL
ln -s $SEAGULL_DIR/lib/data $DEPLOY_DIR/$REVISION_NUM/lib/data
ln -s $SEAGULL_DIR/lib/pear $DEPLOY_DIR/$REVISION_NUM/lib/pear

