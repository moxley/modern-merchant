<?php
/**
 * @package admin
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * @package admin
 */
class admin_Plugin extends plugin_Base
{
    function info()
    {
        return array(
            'title'    => "Administration",
            'version'  => '0.1',
            'author'   => "Moxley Straton",
            'url'      => 'http://www.modernmerchant.org/');
    }
    
    function init()
    {
        mvc_Hooks::registerController('mm', 'mm_Controller');

        mvc_Hooks::registerMenuItem(array('path' => 'admin'));
        
        mvc_Hooks::registerMenuItem(array('path' => 'admin/mm', 'label'=>'MM'));
        mvc_Hooks::registerMenuItem(array('path' => 'admin/mm/about', 'label'=>'About', 'action'=>'mm.about'));
        mvc_Hooks::registerMenuItem(array('path' => 'admin/mm/notes', 'label'=>'Release Notes', 'action'=>'mm.notes'));

        mvc_Hooks::registerMenuItem(array('path' => 'admin/products', 'action'=>null, 'label' => 'Products'));
        mvc_Hooks::registerMenuItem(array('path' => 'admin/products/manage', 'action'=>'product.list', 'label' => 'Manage Products'));
        mvc_Hooks::registerMenuItem(array('path' => 'admin/products/new', 'action'=>'product.new', 'label' => 'New Product'));
        mvc_Hooks::registerMenuItem(array('path' => 'admin/products/newcategory', 'action'=>'category.new', 'label' => 'New Category'));
        mvc_Hooks::registerMenuItem(array('path' => 'admin/products/pricing', 'action'=>'pricing.default', 'label' => 'Pricing'));
        mvc_Hooks::registerMenuItem(array('path' => 'admin/products/newpricing', 'action'=>'pricing.new', 'label' => 'New Pricing'));
        mvc_Hooks::registerMenuItem(array('path' => 'admin/products/imageimport', 'action'=>'bulkimages.promptImport', 'label' => 'Image Import'));

        mvc_Hooks::registerMenuItem(array('path' => 'admin/website', 'action'=>null, 'label' => 'Website'));
        mvc_Hooks::registerMenuItem(array('path' => 'admin/website/orders', 'action'=>'order.list', 'label' => 'Orders'));
        mvc_Hooks::registerMenuItem(array('path' => 'admin/website/neworder', 'action'=>'order.new', 'label' => 'New Order'));
        mvc_Hooks::registerMenuItem(array('path' => 'admin/website/reports', 'action'=>'report.monthly', 'label' => 'Reports'));
        mvc_Hooks::registerMenuItem(array('path' => 'admin/website/content', 'action'=>'content.list', 'label' => 'Content'));

        mvc_Hooks::registerMenuItem(array('path' => 'admin/config', 'action'=>null, 'label' => 'Configuration'));
        mvc_Hooks::registerMenuItem(array('path' => 'admin/config/general', 'action'=>'setting.list', 'label' => 'General Settings'));
        mvc_Hooks::registerMenuItem(array('path' => 'admin/config/payment', 'action'=>'payment.list', 'label' => 'Payment Methods'));
        mvc_Hooks::registerMenuItem(array('path' => 'admin/config/shipping', 'action'=>'shipping.list', 'label' => 'Shipping Methods'));
        mvc_Hooks::registerMenuItem(array('path' => 'admin/config/plugins', 'label'=>'Plugins', 'action'=>'plugin_admin'));

        mvc_Hooks::registerMenuItem(array('path' => 'admin/accounts', 'action'=>null, 'label' => 'Accounts'));
        mvc_Hooks::registerMenuItem(array('path' => 'admin/accounts/editself', 'action'=>'user.editSelf', 'label' => 'Your Account'));
        mvc_Hooks::registerMenuItem(array('path' => 'admin/accounts/users', 'action'=>'user.list', 'label' => 'Users'));
        mvc_Hooks::registerMenuItem(array('path' => 'admin/accounts/adduser', 'action'=>'user.new', 'label' => 'Add User'));
    }
    
    function upgrade_to_0_1() {
        return true;
    }
}
