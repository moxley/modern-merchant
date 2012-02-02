<?php
/**
 * Print the categories, grouped by parent and indented on level.
 */
require_once dirname(__FILE__) . '/../../init.php';
require_once dirname(__FILE__) . '/lib.php';

$cats = cat_getAllByLft();
$parentsById = cat_collect($cats);
echo "Categories:\n";
cat_printLevel($parentsById, 0);
