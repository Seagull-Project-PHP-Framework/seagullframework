#!/bin/bash
# $Id: release.sh,v 1.15 2005/06/24 00:38:54 demian Exp $

# +---------------------------------------------------------------------------+
# | script for automating a Seagull release                                   |
# +---------------------------------------------------------------------------+
# | execute from seagull CVS repository root                                  |
# +---------------------------------------------------------------------------+
##############################
# init vars + binaries
##############################

# binaries
CVS=/usr/bin/cvs
FTP=/usr/bin/ftp


# SF FTP details
FTP_HOSTNAME=upload.sourceforge.net
FTP_USERNAME=anonymous
FTP_PASSWORD=demian@phpkitchen.com
FTP_REMOTE_DIR=incoming

# Get the tag name from the command line:
VERSION=$1
RELEASE=$2
CVS_PASSWD=$3

export CVSROOT=pserver:demian:$CVS_PASSWD@phpkitchen.com:/var/cvs

##############################
# usage
##############################
function usage()
{
      echo ""
      echo "Usage: ./release.sh version release password"
      echo "where \"version\" is the Seagull version (e.g. 0.3.1)"
      echo "and release is the release (e.g. release_0_3_1),"
      echo "and password is the password"  
}

##############################
# check args
##############################
function checkArgs()
{
    # Check that a version tag was specified:
    if [ -z "$VERSION" ]; then
      usage
      exit 1
    fi
    
    # Check that a release tag was specified:
    if [ -z "$RELEASE" ]; then
      usage
      exit 1
    fi
    
    # Check that a password was specified:
    if [ -z "$CVS_PASSWD" ]; then
      usage
      exit 1
    fi    
}

##############################
# check previous versions
##############################
function checkPreviousVersions()
{
    # Check that the release directory doesn't already exist:
    if [ -d "/tmp/seagull*" ]; then
      echo "Removing last seagull export ..."
      rm -rf /tmp/seagull*
    fi
    
    # Check that the release directory doesn't already exist:
    if [ -d "/tmp/seagull-$VERSION" ]; then
      echo "Removing last seagull export ..."
      rm -rf /tmp/seagull-$VERSION
    fi
    
    # Check that the last tarball doesn't exist:
    if [ -e "/tmp/seagull-$VERSION.tar.gz" ]; then
      echo "Removing last seagull tarball ..."
      rm -f /tmp/seagull-$VERSION.tar.gz
    fi

    # Check that the last apiDocs dir doesn't exist:
    if [ -d "/tmp/seagullApiDocs-$VERSION" ]; then
      echo "Removing last seagull apiDocs dir ..."
      rm -rf /tmp/seagullApiDocs-$VERSION
    fi
    
    # Check that the last apiDocs tarball doesn't exist:
    if [ -e "/tmp/seagullApiDocs-$VERSION.tar.gz" ]; then
      echo "Removing last seagull apiDocs tarball ..."
      rm -f /tmp/seagullApiDocs-$VERSION.tar.gz
    fi
}

##############################
# tag release
##############################
function tagRelease()
{
    # tag release
    $CVS -q tag $RELEASE
}

##############################
# export cvs and package
##############################
function exportCvsAndPackage()
{   
    # export release
    $CVS -z3 -d:$CVSROOT -q export -r $RELEASE seagull 
    
    # remove unwanted dirs
    rm -f seagull/TODO.txt
    rm -rf seagull/lib/other/phpthumb
    rm -rf seagull/modules/cart
    rm -rf seagull/modules/rate
    rm -rf seagull/modules/shop
    rm -rf seagull/www/images/shop
    rm -rf seagull/lib/pear/Spreadsheet
    rm -rf seagull/www/themes/default/cart
    rm -rf seagull/www/themes/default/rate
    rm -rf seagull/www/themes/default/shop
    
    #make apiDocs script executable
    chmod 755 seagull/etc/phpDocCli.sh
    
    # rename folder to current release
    mv seagull seagull-$VERSION
    
    # tar and zip
    tar cvf seagull-$VERSION.tar seagull-$VERSION
    gzip seagull-$VERSION.tar
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
put seagull-$VERSION.tar.gz
bye
EOF
}

##############################
# documentation generation
##############################
function generateApiDocs()
{
    seagull-$VERSION/etc/phpDocCli.sh

    # rename folder    
    mv seagullApiDocs seagullApiDocs-$VERSION
}

##############################
# documentation packaging
##############################
function packageApiDocs()
{
    tar cvf seagullApiDocs-$VERSION.tar seagullApiDocs-$VERSION
    gzip -f seagullApiDocs-$VERSION.tar
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
    scp -1 seagull-$VERSION/CHANGELOG.txt demian@phpkitchen.com:/var/www/html/seagull/web/
}

##############################
##############################
# main
##############################
##############################

checkArgs

checkPreviousVersions

tagRelease

# move to tmp dir
cd /tmp

exportCvsAndPackage

uploadToSfWholePackage

#generateApiDocs

#packageApiDocs

#uploadToSfApiDocs

scpChangelogToSglSite

exit 0