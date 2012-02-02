<?php
/**
 * Fix 'lft' and 'rgt' values for the existing categories.
 */
require_once dirname(__FILE__) . '/../../init.php';
require_once dirname(__FILE__) . '/lib.php';

$cats = cat_getAllByLft();
$parentsById = cat_collect($cats);
cat_fix($parentsById);
cat_printLevel($parentsById);
cat_updateAll($parentsById, true);
