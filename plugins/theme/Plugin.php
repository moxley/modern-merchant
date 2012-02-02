<?php
/**
 * @package theme
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class theme_Plugin extends plugin_Base
{
    function info()
    {
        return array(
            'title'   => "Themes",
            'version' => '0.1',
            'author'  => "Moxley Stratton",
            'url'     => "http://www.modernmerchant.org/",
            'depends' => array('mm'));
    }

    function init()
    {
        mvc_Hooks::registerController('theme', 'theme_Controller');
        mvc_Hooks::registerMenuItem(array('path' => 'admin/config/themes', 'label'=>'Themes', 'action'=>'theme.list'));
    }
    
    function install()
    {
        mm_setSetting('theme.admin', 'default.admin');
        mm_setSetting('theme.public', 'default');
        return true;
    }
    
}
