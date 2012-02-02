<?php
/**
 * Remove orphaned items
 * @package scripts
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

include_once('init.php');
$dbh = mm_getDatabase();

// Removed orphaned items where there is no category
$res = $dbh->query('select l.ItemID, l.CategoryID from CategoryLink l ' .
        'left join category c on c.CategoryID=l.CategoryID ' .
        'where c.CategoryID is NULL');
while ($row = $res->fetchAssoc()) {
    print 'Deleting ItemID ' . $row[0] . ", CategoryID $row[1]\n";
    $query = 'delete from item WHERE ItemID=' . intval($row[0]);
    print "$query\n";
    $dbh->execute($query);
    $query = 'delete from CategoryLink WHERE ItemID=' . intval($row[0]);
    print "$query\n";
    $dbh->execute($query);
}
$res->free();

// Remove orphaned CategoryLinks
$res = $dbh->query('select l.ItemID, l.CategoryID from categorylink l ' .
        'left join category c on c.CategoryID=l.CategoryID ' .
        'left join item i on i.ItemID=l.ItemID ' .
        'where c.CategoryID is NULL ' .
        'or i.ItemID is NULL');
while ($row = $res->fetchArray()) {
    print "Deleting categorylink $row[0],$row[1]\n";
    $query = 'delete from categorylink WHERE ItemID=' . intval($row[0]) . ' and CategoryID=' . intval($row[1]);
    print "$query\n";
    $dbh->execute($query);
}
$res->free();

// Remove orphaned Media records
$key = 'filepaths.item_images';
$config = $MM_CONTEXT->getConfig();
$path = $config->get($key);
$d = dir($path);
$i = 0;
$images = array();
while ( false !== ($item=$d->read()) ) {
    if ($item == '.' || $item == '..') continue;
    $images[] = $item;
    $i++;
}
$d->close();
$res = $dbh->query('select Filename,MediaID from media');
$deleteRecords = array();
while ($row = $res->fetchArray()) {
    if (!in_array($row[0], $images)) {
        $deleteRecords[] = $row[1];
    }
}
if ($deleteRecords) {
    $query = 'delete from media where MediaID in (' . implode(',', $deleteRecords) . ')';
    $res = $dbh->execute($query);
}
else {
    print "No media records to delete\n";
}
