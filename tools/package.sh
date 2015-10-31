#!/usr/bin/env bash

SCRIPTPATH=$0
PACKAGE_ROOT="$(dirname "$(dirname "$0")")"
MODULE_ROOT="$PACKAGE_ROOT/src"
TOOLS_PATH=$PACKAGE_ROOT/tools
BUILD_PATH=$PACKAGE_ROOT/build
SINGLESTORE_TMP=$BUILD_PATH/singlestore
MULTISTORE_TMP=$BUILD_PATH/multistore

# change working directory
cd $PACKAGE_ROOT

ZIP=`which zip`
COPY=`which cp`
CREATEDIR=`which mkdir`
GREP=`which grep`
RM=`which rm`
PATCH=`which patch`
CAT=`which cat`


function _require_root_privileges {
    if [ "$(id -u)" != "0" ]; then
        echo "Please run this script as root"
        exit 1
    fi
}

# install zip if it does not exist
if ! hash zip 2>/dev/null; then
    _require_root_privileges
    apt-get install -qq zip
fi

function build_package {
    NAME=$1
    PATH=$BUILD_PATH/$NAME

    echo ""
    echo "   Building $NAME Version"

    if [ -d $PATH ]; then
        $RM -rf $PATH
    fi

    # create directory and copy all files to it
    $CREATEDIR -p $PATH;
    $COPY -rf $PACKAGE_ROOT/{doc,src,composer.json,modman,*.txt,*.md} $PATH

    # retrieve version of config.xml
    VERSION=`$GREP -oPm1 "(?<=<version>)[^<]+" $PATH/src/app/code/community/TSDesigns/MenuBuilder/etc/config.xml`

    # apply patches
    if [ -f $TOOLS_PATH/$NAME.patch ]; then
        $COPY $TOOLS_PATH/$NAME.patch $PATH/apply.patch
        echo "   Applying patches"
        cd $PATH && $PATCH -p0 < apply.patch && $RM apply.patch
    fi

    # make zip file
    ZIPFILE=$BUILD_PATH/TSDesigns_MenuBuilder_${NAME}_v${VERSION}.zip

    if [ -f $ZIPFILE ]; then
        $RM $ZIPFILE
    fi

    cd $PATH
    $ZIP -9 -r -q $ZIPFILE ./
    echo "   Zip file created at $ZIPFILE"
}



echo "   "
echo "   "
echo "   #########################################"
echo "   ###                                   ###"
echo "   ###   Building MenuBuilder Packages   ###"
echo "   ###                                   ###"
echo "   #########################################"
echo "   "
echo "   Package Root:      $PACKAGE_ROOT"
echo "   Module Root:       $MODULE_ROOT"
echo "   Build Path:        $BUILD_PATH"
echo "   Tools Path:        $TOOLS_PATH"
echo "   SingleStore Tmp:   $SINGLESTORE_TMP"
echo "   MultiStore Tmp:    $MULTISTORE_TMP"
echo "   "

build_package SingleStore $SINGLESTORE_TMP
build_package MultiStore $MULTISTORE_TMP
