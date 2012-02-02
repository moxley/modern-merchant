#!/bin/bash

MM_WORKING_DIR="/Users/moxley/workspace/modern/trunk"
VERSIONS_DIR="/Users/moxley/www/vhosts/mmupgrade/versions"
HTDOCS_DIR="/Users/moxley/www/vhosts/mmupgrade/htdocs"
VERSION=0.6.2a1
if [ $1 ]; then
  VERSION=$1
fi

$MM_WORKING_DIR/bin/droptables mmupgrade
rm -rf $HTDOCS_DIR
cp -Rp "$VERSIONS_DIR/modern_$VERSION" $HTDOCS_DIR

chmod 777 $HTDOCS_DIR/mm/public \
  $HTDOCS_DIR/mm/private \
  $HTDOCS_DIR/mm/conf

