#!/bin/bash

MM_WORKING_DIR="/Users/moxley/workspace/modern/trunk"
HTDOCS_DIR="/Users/moxley/www/vhosts/mmupgrade/htdocs"

cd $MM_WORKING_DIR/htdocs
find . \
  -not -name DUMMY \
  -not -name .DS_Store \
  -not -name .svn \
  -not -path "." \
  -not -path "*/.svn*" \
  -not -path "./mm/conf/config.php" \
  -not -path "./mm/private*" \
  -not -path "./mm/public*" \
  | cpio -p -v $HTDOCS_DIR/
mkdir $HTDOCS_DIR/mm/private $HTDOCS_DIR/mm/public
