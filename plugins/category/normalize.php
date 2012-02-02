<?php

/**
 * Normalize CategoryID values in the database so that they
 * count 1,2,3...
 *
 * @package scripts
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

require_once('init.php');

global $MM_CONTEXT;
$dbh = $MM_CONTEXT->getDatabase();
$sql = "select CategoryID from category order by CategoryID";
$res = $dbh->query($sql);
for ($id=1; $record =& $res->fetchAssoc(); $id++) {
    $cu_sql = "update category set CategoryID=? where CategoryID=?";
    $sth = $dbh->prepare($cu_sql);
    $cu_res = $sth->execute(array($id, $record['CategoryID']));
    $cl_sql = "update categorylink set CategoryID=? where CategoryID=?";
    $sth = $dbh->prepare($cl_sql);
    $cl_res = $sth->execute(array($id, $record['CategoryID']));
}

print "Done.\n";
