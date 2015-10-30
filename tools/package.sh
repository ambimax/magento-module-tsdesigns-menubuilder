#!/usr/bin/env bash

function _require_root_privileges {
    if [ "$(id -u)" != "0" ]; then
        echo "Please run this script as root"
        exit 1
    fi
}

function create_zip {
    filename=$1
    cd $PACKAGE_ROOT
    #$ZIP -9 -r $1 ./ -x *tools* -x *.git*  -x *.gitignore*
    $ZIP -9 -r --exclude=*.git* $filename ./
}

function build_package {
    PATH=$1
    if [ ! -d $PATH ]; then
        mkdir -p $PATH;
    fi

    cp -r $PACKAGE_ROOT/{doc,src,composer.json,modman,*.txt,*.md} $PATH
}

# install zip if it does not exist
if ! hash zip 2>/dev/null; then
    _require_root_privileges
    apt-get install -qq zip
fi

PACKAGE_ROOT="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && cd .. && pwd )"
MODULE_ROOT="$PACKAGE_ROOT/src/"
SINGLESTORE_TMP=$PACKAGE_ROOT/tools/build/singlestore
MULTISTORE_TMP=$PACKAGE_ROOT/tools/build/multistore

ZIP=`which zip`

echo "   "
echo "   "
echo "   #########################################"
echo "   ###                                   ###"
echo "   ###   Building MenuBuilder Packages   ###"
echo "   ###                                   ###"
echo "   #########################################"
echo "   "
echo "   Package Root:   $PACKAGE_ROOT"
echo "   Module Root:    $MODULE_ROOT"
echo "   "
echo "   "
echo "   "
echo "   Building MultiStore Version"


build_package $MULTISTORE_TMP
#create_zip tsdesigns_menubuilder_multistore_v1.9.2.zip