#!/bin/bash
# $Id: release.sh,v 1.15 2005/06/24 00:38:54 demian Exp $

# +---------------------------------------------------------------------------+
# | script for automating a Seagull release                                   |
# +---------------------------------------------------------------------------+
# | execute from seagull svn repository root                                  |
# +---------------------------------------------------------------------------+
##############################
# init vars + binaries
##############################

# binaries
SVN=/usr/bin/svn
SCP=/usr/bin/scp
FTP=/usr/bin/ftp

# SF FTP details
FTP_HOSTNAME=upload.sourceforge.net
FTP_USERNAME=anonymous
FTP_PASSWORD=demian@phpkitchen.com
FTP_REMOTE_DIR=incoming

# Get the tag name from the command line:
VERSION=$1
RELEASE=$2
PROJECT_NAME=seagull
SVN_REPO_ROOT=http://seagull.phpkitchen.com:8172/svn/seagull

##############################
# usage
##############################
function usage()
{
      echo "Usage: ./release.sh VERSION RELEASE"
      echo "  where VERSION is the $PROJECT_NAME version (e.g. 0.4.4)"
      echo "  and RELEASE is the release (e.g. release_0_4_4)"
}

##############################
# check args
##############################
function checkArgs()
{
    # Check that arguments were specified:
    if [ -z $VERSION ] || [ -z $RELEASE ]; then
      usage
      exit 1
    fi
}

##############################
# check previous versions
##############################
function checkPreviousVersions()
{
    # Remove unuseful old stuff
    echo "Removing last seagull export ..."
    rm -rf /tmp/seagull-$VERSION
    
    echo "Removing last seagull tarball ..."
    rm -f /tmp/seagull-$VERSION.tar.gz

    echo "Removing last seagull apiDocs dir ..."
    rm -rf /tmp/seagullApiDocs-$VERSION
    
    echo "Removing last seagull apiDocs tarball ..."
    rm -f /tmp/seagullApiDocs-$VERSION.tar.gz
}

##############################
# tag release
##############################
function tagRelease()
{
    $SVN copy $SVN_REPO_ROOT/trunk $SVN_REPO_ROOT/tags/$RELEASE
}

##############################
# export svn and package
##############################
function exportSvnAndPackage()
{   
    $SVN export $SVN_REPO_ROOT/trunk -r $RELEASE 
    
    #rename trunk to project name
    mv trunk $PROJECT_NAME
    
    # remove unwanted dirs
    rm -f $PROJECT_NAME/TODO.txt
    rm -f $PROJECT_NAME/etc/badBoyWebTests.bb
    rm -f $PROJECT_NAME/etc/cvsNightlyBuild.sh
    rm -f $PROJECT_NAME/etc/demoReload.sh
    rm -f $PROJECT_NAME/etc/generatePackage.php
    rm -f $PROJECT_NAME/etc/phpDocWeb.ini
    rm -f $PROJECT_NAME/etc/release.sh
    rm -rf $PROJECT_NAME/lib/other/phpthumb
    rm -rf $PROJECT_NAME/lib/pear/Spreadsheet
    rm -rf $PROJECT_NAME/lib/SGL/tests       
    rm -rf $PROJECT_NAME/modules/cart
    rm -rf $PROJECT_NAME/modules/rate
    rm -rf $PROJECT_NAME/modules/shop
    rm -rf $PROJECT_NAME/modules/user/tests
    rm -f $PROJECT_NAME/www/ errorTests.php     
    rm -rf $PROJECT_NAME/www/images/shop
    rm -rf $PROJECT_NAME/www/themes/default/cart
    rm -rf $PROJECT_NAME/www/themes/default/rate
    rm -rf $PROJECT_NAME/www/themes/default/shop
    
    # rename folder to current release
    mv $PROJECT_NAME $PROJECT_NAME-$VERSION
    
    # tar and zip
    tar cvf $PROJECT_NAME-$VERSION.tar $PROJECT_NAME-$VERSION \
        | gzip -f > $PROJECT_NAME-$VERSION.tar.gz
}

##############################
# upload whole package release to SF
##############################
function uploadToSfWholePackage()
{
    # ftp upload to SF
    
    $FTP -nd $FTP_HOSTNAME <<EOF
user $FTP_USERNAME $FTP_PASSWORD
bin
has
cd $FTP_REMOTE_DIR
put $PROJECT_NAME-$VERSION.tar.gz
bye
EOF
}

##############################
# documentation generation
##############################
function generateApiDocs()
{
    #make apiDocs script executable
    chmod 755 $PROJECT_NAME-$VERSION/etc/phpDocCli.sh
    
    #execute phpDoc
    $PROJECT_NAME-$VERSION/etc/phpDocCli.sh

    mv seagullApiDocs seagullApiDocs-$VERSION
}

##############################
# documentation packaging
##############################
function packageApiDocs()
{
    tar cvf seagullApiDocs-$VERSION.tar seagullApiDocs-$VERSION \
        | gzip -f > seagullApiDocs-$VERSION.tar.gz
}

##############################
# upload Api docs
##############################
function uploadToSfApiDocs()
{
    # ftp upload to SF
    
    $FTP -nd $FTP_HOSTNAME <<EOF
user $FTP_USERNAME $FTP_PASSWORD
bin
has
cd $FTP_REMOTE_DIR
put seagullApiDocs-$VERSION.tar.gz
bye
EOF
}

##############################
# scp changelog to sgl site
##############################
function scpChangelogToSglSite()
{
    scp -1 $PROJECT_NAME-$VERSION/CHANGELOG.txt \
        demian@phpkitchen.com:/var/www/html/seagull/web/
}

##############################
##############################
# main
##############################
##############################

checkArgs

checkPreviousVersions

#tagRelease

cd /tmp

exportSvnAndPackage

uploadToSfWholePackage

generateApiDocs

packageApiDocs

uploadToSfApiDocs

scpChangelogToSglSite

exit 0
