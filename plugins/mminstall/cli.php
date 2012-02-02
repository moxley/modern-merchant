<?php
/**
 * Include file for the CLI version of the installer.
 *
 * @package mminstall
 */

require dirname(__FILE__) . '/init.php';
$installer = new mminstall_CliInstaller;
$installer->install();

