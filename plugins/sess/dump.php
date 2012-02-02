<?php

/**
 * Show session data for a given session_id.
 *
 * @package scripts
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

require 'init.php';

$session_id = @$_SERVER['argv'][1];
if (!$session_id) {
    fprintf(STDERR, "Usage: scripts/sessdump SESSION_ID\n");
    exit(2);
}

$db = mm_getDatabase();
$sess_row = $db->getOneAssoc("SELECT * FROM mm_session WHERE session_id=?", array($session_id));
if (!$sess_row) {
    fprintf(STDERR, "Record not found.\n");
    exit(1);
}

echo "data:\n";
echo $sess_row['data'] . "\n";
echo "\nexport:\n";
$sess = mm_decodeSession($sess_row['data']);
var_export($sess);
echo "\nDone.\n";
