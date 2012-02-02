<?php
/**
 * Main front script
 *
 * Welcome to Modern Merchant!
 *
 * Now that you're here, you may be interested in learning about Modern Merchant's architecture
 * and file tree. Almost everything is inside the <pre>mm/</pre> folder. Low-level configuration
 * is defined in: <pre>mm/conf/config.php</pre> Day-to-day configuration is managed by the database
 * and is accessible from the admin web application.
 *
 * The bulk of Modern Merchant's functionality is provided by the plugins inside <pre>mm/plugins/</pre>
 * It's easy to add new plugins and it's not too hard to create your own.
 *
 * If you're interested in changing the design of the storefront and shopping cart, check out
 * <pre>mm/themes/README.txt</pre>.
 *
 * @package Default
 * @copyright 2004 - 2008 Moxley Stratton
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
require './init.php';
mm_setConfigValue('default_action', mm_getSetting('action.home'));
mvc_Controller::runRequest();
