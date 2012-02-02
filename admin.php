<?php

/**
 * Administration front script
 * 
 * @package default
 * @copyright (C) 2004 - 2007 Moxley Stratton
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

require './init.php';

mm_setConfigValue('controllers.default', 'admin');
mvc_Controller::runRequest();
