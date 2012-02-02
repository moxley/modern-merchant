<?php
/**
 * @package mm
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * @package mm
 */
class mm_Plugin extends plugin_Base
{
    function info()
    {
        return array(
            'title'    => "Modern Merchant Core",
            'version'  => '0.3',
            'author'   => "Moxley Stratton",
            'url'      => 'http://www.modernmerchant.org/',
            'depends'  => array('db', 'sess', 'setting', 'theme', 'plugin')
        );
    }
    
    function init()
    {
        mvc_Hooks::registerMenuItem(array('path' => 'admin/mm/tester', 'action'=>'mm.tester', 'label' => 'Code Tester'));
    }
    
    function install()
    {
        mm_setSetting('action.home', 'content.show?name=home');
        mm_setSetting('date_format', 'm/d/Y');
        mm_setSetting('datetime_format', 'm/d/Y \a\\t g:i:s a');
        mm_setSetting('site.name', 'Modern Merchant Storefront');
        mm_setSetting('site.noreply', 'noreply@example.com');
        mm_setSetting('webmaster.notification', 'example@example.com');
        mm_setSetting('plugins.mm.http_post.proxy.active', false);
        mm_setSetting('plugins.mm.http_post.proxy.host',   "proxy.shr.secureserver.net");
        mm_setSetting('plugins.mm.http_post.proxy.port',   3128);
        return true;
    }
    
    function upgrade_to_0_2()
    {
        mm_setSetting('date_format', 'm/d/Y');
        mm_setSetting('datetime_format', 'm/d/Y \a\\t g:i:s a');
        $action_home = mm_getSetting('action.home');
        if ($action_home == 'Catalog.products2Tier') {
            mm_setSetting('action.home', 'catalog.products');
        }
        return true;
    }
    
    function upgrade_to_0_3()
    {
        mm_setSetting('plugins.mm.http_post.proxy.active', false);
        mm_setSetting('plugins.mm.http_post.proxy.host',   "proxy.shr.secureserver.net");
        mm_setSetting('plugins.mm.http_post.proxy.port',   3128);
        return true;
    }
}
