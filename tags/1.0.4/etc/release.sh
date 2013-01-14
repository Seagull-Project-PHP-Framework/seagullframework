#!/bin/bash
# $Id: release.sh,v 1.15 2005/06/24 00:38:54 demian Exp $

# +---------------------------------------------------------------------------+
# | script for automating a Seagull release                                   |
# +---------------------------------------------------------------------------+
# | execute from seagull svn repository root                                  |
# +---------------------------------------------------------------------------+
# | Usage: ./release.sh revision_num release_name [min]                       |
# +---------------------------------------------------------------------------+

##############################
# init vars + binaries
##############################

# binaries
SVN=/usr/bin/svn
SCP=/usr/bin/scp
FTP=/usr/bin/ftp
PHP=/usr/bin/php
PEAR=/usr/bin/pear

# SF FTP details
FTP_HOSTNAME=upload.sourceforge.net
FTP_USERNAME=anonymous
FTP_PASSWORD=demian@phpkitchen.com
FTP_REMOTE_DIR=incoming

# Get the tag name from the command line:
REVISION_NUM=$1
RELEASE_NAME=$2
MINIMAL_INSTALL=$3
PROJECT_NAME=seagull
#SVN_REPO_LEAF_FOLDER_NAME=branches/0.6-bugfix
SVN_REPO_LEAF_FOLDER_NAME=trunk
SVN_REPO_URL=http://svn.seagullproject.org/svn/seagull/$SVN_REPO_LEAF_FOLDER_NAME

SVN_REPO_TAGS_URL=http://svn.seagullproject.org/svn/seagull/tags


##############################
# usage
##############################
function usage()
{
      echo ""
      echo "Usage: ./release.sh revision_num release_name [minimal_install]"
      echo "    where \"revision_num\" is the $PROJECT_NAME svn revision number (e.g. 226)"
      echo "    and \"release_name\" is the release name (e.g. 0.4.5) which gives the full name \"seagull-0.4.5\""
      echo "    the optional 3rd parameter, 'min', will create a minimal install"
}

##############################
# check args
##############################
function checkArgs()
{
    # Check that arguments were specified:
    if [ -z $REVISION_NUM ] || [ -z $RELEASE_NAME ]; then
      usage
      exit 1
    fi
}

##############################
# check previous versions
##############################
function checkPreviousVersions()
{
    # Check that the release directory doesn't already exist (fresh export):
    if [ -d "/tmp/$SVN_REPO_LEAF_FOLDER_NAME" ]; then
      echo "Removing last $SVN_REPO_LEAF_FOLDER_NAME export ..."
      rm -rf /tmp/$SVN_REPO_LEAF_FOLDER_NAME
    fi

    # Check that the release directory doesn't already exist:
    if [ -d "/tmp/$PROJECT_NAME-$RELEASE_NAME" ]; then
      echo "Removing last $PROJECT_NAME-$RELEASE_NAME renamed export ..."
      rm -rf /tmp/$PROJECT_NAME-$RELEASE_NAME
    fi

    # Check that the release directory doesn't already exist:
    if [ -d "/tmp/$PROJECT_NAME" ]; then
      echo "Removing last $PROJECT_NAME renamed export ..."
      rm -rf /tmp/$PROJECT_NAME
    fi

    # Check that the minimal release directory doesn't already exist:
    if [ -d "/tmp/$PROJECT_NAME-$RELEASE_NAME-minimal" ]; then
      echo "Removing last $PROJECT_NAME-$RELEASE_NAME-minimal renamed export ..."
      rm -rf /tmp/$PROJECT_NAME-$RELEASE_NAME-minimal
    fi

    # Check that the last tarball doesn't exist:
    if [ -e "/tmp/$PROJECT_NAME-$RELEASE_NAME.tar.gz" ]; then
      echo "Removing last $PROJECT_NAME-$RELEASE_NAME.tar.gz ..."
      rm -f /tmp/$PROJECT_NAME-$RELEASE_NAME.tar.gz
    fi

    # Check that the last tarball doesn't exist:
    if [ -e "/tmp/$PROJECT_NAME-$RELEASE_NAME-minimal.tar.gz" ]; then
      echo "Removing last $PROJECT_NAME-$RELEASE_NAME-minimal.tar.gz ..."
      rm -f /tmp/$PROJECT_NAME-$RELEASE_NAME-minimal.tar.gz
    fi

    # Check that the last apiDocs dir doesn't exist:
    if [ -d "/tmp/seagullApiDocs-$RELEASE_NAME" ]; then
      echo "Removing last seagullApiDocs-$RELEASE_NAME ..."
      rm -rf /tmp/seagullApiDocs-$RELEASE_NAME
    fi

    # Check that the last apiDocs tarball doesn't exist:
    if [ -e "/tmp/seagullApiDocs-$RELEASE_NAME.tar.gz" ]; then
      echo "Removing last seagullApiDocs-$RELEASE_NAME.tar.gz ..."
      rm -f /tmp/seagullApiDocs-$RELEASE_NAME.tar.gz
    fi
}

##############################
# tag release
##############################
function tagRelease()
{
    # tag release
    $SVN copy $SVN_REPO_URL $SVN_REPO_TAGS_URL/$RELEASE_NAME -m "creating tag for $REVISION_NUM"
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
# create minimal flag
##############################
function createMinimalFlag()
{
    if [ $MINIMAL_INSTALL ]; then
      touch $PROJECT_NAME/MINIMAL_INSTALL.txt
    fi
}


##############################
# prune minimal
##############################
function pruneMinimal()
{
    # remove unwanted files
    rm -f $PROJECT_NAME/etc/convertCategories.php
    rm -f $PROJECT_NAME/etc/Flexy2Smarty.php
    rm -f $PROJECT_NAME/etc/flexy2SmartyRunner.php
    rm -f $PROJECT_NAME/etc/generatePackageSimpleTest.php
    rm -f $PROJECT_NAME/etc/generatePearPackageXml.php
    rm -f $PROJECT_NAME/etc/mysql5_field_test.php
    rm -f $PROJECT_NAME/etc/ociTableDrop.sh
    rm -f $PROJECT_NAME/etc/mysql5_field_test.php
    rm -f $PROJECT_NAME/etc/phpDocCli.sh
    rm -f $PROJECT_NAME/etc/phpDocWeb.ini
    rm -f $PROJECT_NAME/etc/release.sh
    rm -f $PROJECT_NAME/etc/seagull-pgsql-createDB.sh
    rm -f $PROJECT_NAME/etc/sglBridge.php
    rm -rf $PROJECT_NAME/lib/data/ary.countries.af.php
    rm -rf $PROJECT_NAME/lib/data/ary.countries.ar.php
    rm -rf $PROJECT_NAME/lib/data/ary.countries.da.php
    rm -rf $PROJECT_NAME/lib/data/ary.countries.de.php
    rm -rf $PROJECT_NAME/lib/data/ary.countries.fr.php
    rm -rf $PROJECT_NAME/lib/data/ary.countries.hu.php
    rm -rf $PROJECT_NAME/lib/data/ary.countries.it.php
    rm -rf $PROJECT_NAME/lib/data/ary.countries.pl.php
    rm -rf $PROJECT_NAME/lib/data/ary.countries.pt.php
    rm -rf $PROJECT_NAME/lib/data/ary.countries.sv.php
    rm -rf $PROJECT_NAME/lib/data/ary.countries.tr.php
    rm -rf $PROJECT_NAME/lib/data/ary.countries.ru.php
    rm -rf $PROJECT_NAME/lib/data/ary.states.de.php
    rm -rf $PROJECT_NAME/lib/data/ary.states.it.php
    rm -rf $PROJECT_NAME/lib/data/ary.states.pl.php
    rm -rf $PROJECT_NAME/lib/pear/I18Nv2.php
    rm -rf $PROJECT_NAME/lib/pear/OLE.php
    rm -rf $PROJECT_NAME/lib/pear/Translation2.php
    rm -rf $PROJECT_NAME/lib/pear/Text/Statistics.php
    rm -rf $PROJECT_NAME/lib/pear/Text/Word.php

    # remove unwanted dirs
    rm -rf $PROJECT_NAME/docs
    rm -rf $PROJECT_NAME/etc/mtce
    rm -rf $PROJECT_NAME/etc/sql_upgrades
    rm -rf $PROJECT_NAME/lib/pear/Calendar
    rm -rf $PROJECT_NAME/lib/pear/HTML/AJAX
    rm -rf $PROJECT_NAME/lib/pear/HTTP/Download
    rm -rf $PROJECT_NAME/lib/pear/HTTP/Request
    rm -rf $PROJECT_NAME/lib/pear/I18Nv2
    rm -rf $PROJECT_NAME/lib/pear/Image
    rm -rf $PROJECT_NAME/lib/pear/Net/UserAgent
    rm -rf $PROJECT_NAME/lib/pear/OLE
    rm -rf $PROJECT_NAME/lib/pear/PHP
    rm -rf $PROJECT_NAME/lib/pear/Translation2
    rm -rf $PROJECT_NAME/lib/pear/Validate
    rm -rf $PROJECT_NAME/lib/SGL/tests
    rm -rf $PROJECT_NAME/modules/comment
    rm -rf $PROJECT_NAME/modules/comment2
    rm -rf $PROJECT_NAME/modules/contactus
    rm -rf $PROJECT_NAME/modules/documentor
    rm -rf $PROJECT_NAME/modules/emailqueue
    rm -rf $PROJECT_NAME/modules/event
    rm -rf $PROJECT_NAME/modules/export
    rm -rf $PROJECT_NAME/modules/faq
    rm -rf $PROJECT_NAME/modules/gallery2
    rm -rf $PROJECT_NAME/modules/googlemaps
    rm -rf $PROJECT_NAME/modules/guestbook
    rm -rf $PROJECT_NAME/modules/media
    rm -rf $PROJECT_NAME/modules/messaging
    rm -rf $PROJECT_NAME/modules/newsletter
    rm -rf $PROJECT_NAME/modules/publisher
    rm -rf $PROJECT_NAME/modules/randommsg
    rm -rf $PROJECT_NAME/modules/s9ywrapper
    rm -rf $PROJECT_NAME/modules/simplesite
    rm -rf $PROJECT_NAME/modules/siteexporter
    rm -rf $PROJECT_NAME/modules/sitemap
    rm -rf $PROJECT_NAME/modules/translation
    rm -rf $PROJECT_NAME/modules/user/tests
    rm -rf $PROJECT_NAME/tests
    rm -rf $PROJECT_NAME/www/js/html_ajax
    rm -rf $PROJECT_NAME/www/js/jscalendar
    rm -rf $PROJECT_NAME/www/js/lightbox
    rm -rf $PROJECT_NAME/www/js/scriptaculous
    rm -rf $PROJECT_NAME/www/savant
    rm -rf $PROJECT_NAME/www/smarty

    #remove non english language
    moduleList=`ls $PROJECT_NAME/modules`;
    for moduleName in $moduleList;
    do
        langList=`ls $PROJECT_NAME/modules/$moduleName/lang`;
        for langName in $langList;
        do
            if [ $langName != "english-iso-8859-15.php" ]; then
                rm -f $PROJECT_NAME/modules/$moduleName/lang/$langName;
            fi
        done;
    done;

    #remove non-mysql data files
    for moduleName in $moduleList;
    do
        dataList=`ls $PROJECT_NAME/modules/$moduleName/data`;
        pg_pattern='pg';
        oci_pattern='oci';
        for file in $dataList;
        do
            if echo "$file" | grep -q "$pg_pattern"; then
                rm -f $PROJECT_NAME/modules/$moduleName/data/$file;
            elif echo "$file" | grep -q "$oci_pattern"; then
                rm -f $PROJECT_NAME/modules/$moduleName/data/$file;
            fi
        done;
    done;
}

##############################
# create tarball
##############################
function createTarball()
{
    # rename folder to current release
    if [ $MINIMAL_INSTALL ]; then
        ARCHIVE_NAME=$PROJECT_NAME-$RELEASE_NAME-minimal
    else
        ARCHIVE_NAME=$PROJECT_NAME-$RELEASE_NAME
    fi
    mv $PROJECT_NAME $ARCHIVE_NAME

    # tar and zip
    tar cvf $ARCHIVE_NAME.tar $ARCHIVE_NAME
    gzip -f $ARCHIVE_NAME.tar
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
put $ARCHIVE_NAME.tar.gz
bye
EOF
}

##############################
# documentation generation
##############################
function generateApiDocs()
{
    #execute phpDoc
    $ARCHIVE_NAME/etc/phpDocCli.sh

    # rename folder
    mv seagullApiDocs seagullApiDocs-$RELEASE_NAME
}

##############################
# documentation packaging
##############################
function packageApiDocs()
{
    tar cvf seagullApiDocs-$RELEASE_NAME.tar seagullApiDocs-$RELEASE_NAME
    gzip -f seagullApiDocs-$RELEASE_NAME.tar
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
put seagullApiDocs-$RELEASE_NAME.tar.gz
bye
EOF
}

##############################
# scp api docs to sgl site
##############################
function scpApiDocsToSglSite()
{
    scp seagullApiDocs-$RELEASE_NAME.tar.gz demian@phpkitchen.com:/var/www/seagull_api/
}

##############################
# build minimal PEAR package
##############################
function buildMinimalPearPackage()
{
    # remove unwanted files
    #rm -rf $PROJECT_NAME-$RELEASE_NAME/lib/SGL/tests
    #rm -rf $PROJECT_NAME-$RELEASE_NAME/modules/user/tests
    #rm -rf $PROJECT_NAME-$RELEASE_NAME/package.xml
    #rm -rf $PROJECT_NAME-$RELEASE_NAME/package2.xml
    rm -rf $PROJECT_NAME-$RELEASE_NAME/Seagull-$RELEASE_NAME.tgz

    # remove all but core modules
#    rm -rf $PROJECT_NAME-$RELEASE_NAME/modules/contactus
#    rm -rf $PROJECT_NAME-$RELEASE_NAME/modules/documentor
#    rm -rf $PROJECT_NAME-$RELEASE_NAME/modules/export
#    rm -rf $PROJECT_NAME-$RELEASE_NAME/modules/faq
#    rm -rf $PROJECT_NAME-$RELEASE_NAME/modules/gallery2
#    rm -rf $PROJECT_NAME-$RELEASE_NAME/modules/guestbook
#    rm -rf $PROJECT_NAME-$RELEASE_NAME/modules/messaging
#    rm -rf $PROJECT_NAME-$RELEASE_NAME/modules/newsletter
#    rm -rf $PROJECT_NAME-$RELEASE_NAME/modules/publisher
#    rm -rf $PROJECT_NAME-$RELEASE_NAME/modules/randommsg
#    rm -rf $PROJECT_NAME-$RELEASE_NAME/modules/s9ywrapper



#    rm -rf $PROJECT_NAME-$RELEASE_NAME/www/themes/default/blog
#    rm -rf $PROJECT_NAME-$RELEASE_NAME/www/themes/default/publisher
#    rm -rf $PROJECT_NAME-$RELEASE_NAME/www/themes/default/gallery2

#    rm -rf $PROJECT_NAME-$RELEASE_NAME/www/themes/savant
#    rm -rf $PROJECT_NAME-$RELEASE_NAME/www/themes/smarty

    # copy PEAR overrides into root
    cp $PROJECT_NAME-$RELEASE_NAME/lib/pear/HTML/Tree.php $PROJECT_NAME-$RELEASE_NAME/
    cp $PROJECT_NAME-$RELEASE_NAME/lib/pear/DB/db2_SGL.php $PROJECT_NAME-$RELEASE_NAME/
    cp $PROJECT_NAME-$RELEASE_NAME/lib/pear/DB/maxdb_SGL.php $PROJECT_NAME-$RELEASE_NAME/
    cp $PROJECT_NAME-$RELEASE_NAME/lib/pear/DB/mysql_SGL.php $PROJECT_NAME-$RELEASE_NAME/
    cp $PROJECT_NAME-$RELEASE_NAME/lib/pear/DB/oci8_SGL.php $PROJECT_NAME-$RELEASE_NAME/
    cp $PROJECT_NAME-$RELEASE_NAME/lib/pear/PEAR/Frontend/WebSGL.php $PROJECT_NAME-$RELEASE_NAME/
    cp $PROJECT_NAME-$RELEASE_NAME/lib/pear/PEAR/Command/RemoteSGL.php $PROJECT_NAME-$RELEASE_NAME/
    cp $PROJECT_NAME-$RELEASE_NAME/lib/pear/PEAR/Command/RemoteSGL.xml $PROJECT_NAME-$RELEASE_NAME/

    # setup PEAR env
    $PEAR config-set php_dir /usr/local/lib/php

    # remove previous install
    $PEAR uninstall phpkitchen/Seagull
    $PEAR uninstall phpkitchen/Seagull_default
    $PEAR uninstall phpkitchen/Seagull_navigation
    $PEAR uninstall phpkitchen/Seagull_user

    # create package.xml
    $PHP $PROJECT_NAME-$RELEASE_NAME/etc/generatePearPackageXml.php make $RELEASE_NAME

    # generate package
    $PEAR package -n /tmp/$PROJECT_NAME-$RELEASE_NAME/package2.xml

    mv Seagull-$RELEASE_NAME.tgz /tmp/$PROJECT_NAME-$RELEASE_NAME
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

exportSvn

createMinimalFlag

if [ $MINIMAL_INSTALL ]; then
    pruneMinimal
fi

createTarball

#uploadToSfWholePackage

#generateApiDocs

#packageApiDocs

#uploadToSfApiDocs

#scpApiDocsToSglSite

#buildMinimalPearPackage

exit 0