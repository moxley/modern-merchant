<?php
/**
 * @package mminstall
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

// TODO Rename this file to 'web.php'

require dirname(__FILE__) . '/init.php';


mm_setConfigValue('controllers.default', 'mminstall');
mvc_Controller::runRequest();
