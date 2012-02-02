<?php
/**
 * Installer front script
 * 
 * @package default
 * @copyright (C) 2004 - 2007 Moxley Stratton
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

$ver = phpversion();
if (version_compare($ver, "5.1", "<")) {
    die("Modern Merchant requires PHP version 5.1 or later. You have $ver. Sorry");
}
else {
    include './plugins/mminstall/main.php';
}
