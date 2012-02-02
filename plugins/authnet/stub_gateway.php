<?php
/**
 * @package authnet
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
require_once '../../init.php';

mm_log('In stub_gateway.php');

header("Content-Type: text/plain");
echo '"1","1","1","(TESTMODE) This transaction has been approved.","000000","P","0","","","20.00","CC","auth_capture","","Moxley","Stratton","","100 Main St. Suite 100","Portland","OR","97212","","503-555-6109","","","","","","","","","","","","","","","","B8B29474DD0BA7ED60B066ABB9D49FEC","","","","","","","","","","","","","","","","","","","","","","","","","","","","","",""';
