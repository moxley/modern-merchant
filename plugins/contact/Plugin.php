<?php
/**
 * @package contact
 * @copyright (C) 2007 AlchemyWest
 * @copyright (C) 2007 Modern Merchant
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * 
 */

/**
 * Contact Us plugin.
 */
class contact_Plugin extends plugin_Base
{
    function info()
    {
        return array(
            'title'   => 'Contact Plugin',
            'version' => '0.1',
            'author'  => 'Charles Courchaine',
            'url'     => 'http://www.alchemywest.com/',
            'depends' => array('mm'));
    }

    /**
     * Called automatically for each request if the plugin is active.
     */
    function init()
    {
        //nothing required for this plug-in
    }

    /**
     * Called automatically when Modern Merchant is installed, and when user
     * requests that the plugin be installed.
     */
    function install()
    {
        return true;
    }

    /**
     * Called automatically when user requests that the plugin to be uninstalled.
     */
    function uninstall()
    {
        return true;
    }
}
