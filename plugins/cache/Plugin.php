<?php
/**
 * @package cache
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * This plugin is not ready for turn-key use.
 * 
 * @package cache
 */
class cache_Plugin extends plugin_Base
{
    function info()
    {
        return array(
            'title'        => "Caching for Performance",
            'version'      => '0.0',
            'author'       => "Moxley Stratton",
            'url'          => "http://www.modernmerchant.org/",
            'depends'      => array(),
            'auto_install' => false);
    }
    
    function init() {
        mvc_Hooks::registerMenuItem(array('path' => 'admin/website/caching', 'action'=>'cache_admin.clearPage', 'label' => 'Clear Page Cache'));
    }
}
