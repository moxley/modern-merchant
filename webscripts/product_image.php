<?php

/**
 * @package webscript
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

require_once( 'init.php' );

$user = mm_getUser();
if (!$user->isAdmin()) redirect(mm_actionToUri(mm_getConfigValue('actions.admin_login')));

$media_controller = new controller_MediaController($MM_CONTEXT);
$media_controller->runProductStreamAction();
$output =& $media_controller->getOutput();
header("Content-Type: ".$output['record']['mime_type']);
print $output['record']['image_data'];
