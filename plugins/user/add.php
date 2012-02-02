<?php
/**
 * Command-line script for adding a user
 * 
 * @package scripts
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

include_once '../init.php';
ini_set('display_errors', true);
$user = new user_User;
if (count($_SERVER['argv']) < 4) {
    die("Usage: php add_user.php USERNAME PASSWORD TYPE\n");
}
$user->username = $_SERVER['argv'][1];
$user->password = $_SERVER['argv'][2];
$user->type = $_SERVER['argv'][3]; // 2 = "admin"
$dao = new user_UserDAO;
$dao->add($user);

print "Added user '{$user->username}'.\n";
