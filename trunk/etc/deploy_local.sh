#!/bin/bash

# +---------------------------------------------------------------------------+
# | script for deploying a Seagull release                                    |
# +---------------------------------------------------------------------------+
# | execute from seagull svn repository root                                  |
# +---------------------------------------------------------------------------+
# | Usage: ./deploy_local.sh revision_num  [live]                             |
# +---------------------------------------------------------------------------+

# binaries
SVN=/sw/bin/svn
SCP=/usr/bin/scp
SSH=/usr/bin/ssh

# args
REVISION_NUM=$1
MODE_TEST=$2

USER=demian
DOMAIN=example.com
PROJECT_NAME=Seagull
SVN_REPO_URL=http://example.com/path/to/repo


##############################
# usage
##############################
function usage()
{
      echo ""
      echo "Usage: ./deploy.sh revision_num"
      echo "    where \"revision_num\" is the $PROJECT_NAME svn revision number (e.g. 226)"
}

##############################
# check args
##############################
function checkArgs()
{
    # Check that arguments were specified:
    if [ -z $REVISION_NUM ] ; then
      usage
      exit 1
    fi

    # check if this is a staging deployment
    if [ -z $MODE_TEST ] ; then
        IS_LIVE=0;
    else
        IS_LIVE=1;
    fi
}


##############################
# export svn
##############################
function exportSvn()
{
    # export release
    $SVN export --force $SVN_REPO_URL -r $REVISION_NUM $PROJECT_NAME
}

##############################
# remove unnecessary files
##############################
function slimDown()
{
    rm -rf $PROJECT_NAME/tests
    rm - f $PROJECT_NAME/www/setup.php
    # remove tests folders in modules
}

##############################
# create tarball
##############################
function createTarball()
{
    # rename folder to current release
    ARCHIVE_NAME=$PROJECT_NAME-$REVISION_NUM

    mv $PROJECT_NAME $ARCHIVE_NAME

    # tar and zip
    tar cvf $ARCHIVE_NAME.tar $ARCHIVE_NAME
    gzip -f $ARCHIVE_NAME.tar
}

##############################
# scp tarball to kindo server
##############################
function scpTarballToServer()
{
    $SCP $ARCHIVE_NAME.tar.gz $USER@$DOMAIN:/tmp/
}


##############################
# invoke remote script
##############################
function executeRemoteScript()
{
    if (( $IS_LIVE )) ; then
        $SSH -e none -T $USER@$DOMAIN -v "./deploy_remote.sh $REVISION_NUM live"
    else
        $SSH -e none -T $USER@$DOMAIN -v "./deploy_remote.sh $REVISION_NUM"
    fi
}


##############################
# cleanup files
##############################
function cleanup
{
    rm -rf /tmp/$PROJECT_NAME-$REVISION_NUM
    rm -f /tmp/$ARCHIVE_NAME.tar.gz
}













##############################
##############################
# MAIN
##############################
##############################

checkArgs

# move to tmp dir
cd /tmp

exportSvn

slimDown

createTarball

scpTarballToServer

executeRemoteScript

cleanup

