<?php
/**
 * @package customer
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * @package customer
 */
class customer_Plugin extends plugin_Base
{
    /**
     * Plugin information.
     */
    function info()
    {
        return array(
            'title'   => 'Customer',
            'version' => '0.1',
            'author'  => 'Moxley Stratton',
            'url'     => 'http://www.modernmerchant.org/',
            'depends' => array('mm'));
    }
    
    function install()
    {
        $db = mm_getDatabase();
        $drop = "DROP TABLE IF EXISTS mm_customer";
        $db->execute($drop);
        
        $create = "CREATE TABLE mm_customer (
            id integer not null auto_increment,
            billing_address_id integer,
            shipping_address_id integer,
            user_id integer,
            created_on datetime,
            PRIMARY KEY (id)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8";
        $db->execute($create);
        
        return true;
    }
    
    function upgrade_to_0_1()
    {
        $db = mm_getDatabase();
        $db->execute("DROP TABLE mm_customer_seq");

        $db->execute("ALTER TABLE mm_customer CONVERT TO CHARACTER SET utf8");
        $db->execute("ALTER TABLE mm_customer CHANGE customer_id id INT NOT NULL auto_increment");
        $db->execute("ALTER TABLE mm_customer DROP primary_address_id");
        $db->execute("ALTER TABLE mm_customer ADD user_id integer");
        $db->execute("ALTER TABLE mm_customer ADD created_on datetime");
        
        return true;
    }
    
    /**
     * Called automatically for each request, if the plugin is active.
     */
    function init()
    {
        mvc_Hooks::registerMenuItem(array('path' => 'admin/website/customers', 'action'=>'customer_admin', 'label' => 'Customers'));
        mvc_Hooks::addListener('user.login',         array($this, 'onUserLogin'));
        mvc_Hooks::addListener('user.logout',        array($this, 'onUserLogout'));
        mvc_Hooks::addListener('cart.created',       array($this, 'onCartCreated'));
        mvc_Hooks::addListener('cart.before_create_order', array($this, 'beforeCreateOrder'));
    }
    
    /**
     * Load customer into user's session.
     */
    function onUserLogin($user)
    {
        $customer = customer_Customer::fetchByUser($user);
        if ($customer) {
            mm_setCustomer($customer);
            if ($cart = mm_getCart()) {
                if (!$cart->billing->is_valid) {
                    $cart->billing = $customer->billing;
                }
                if (!$cart->shipping->is_valid) {
                    $cart->shipping = $customer->shipping;
                }
                $cart->save();
            }
        }
    }
    
    /**
     * Remove customer from user's session.
     */
    function onUserLogout($user)
    {
        mm_setCustomer(null);
        
        // Remove personal information
        $cart = mm_getCart();
        $cart->billing = null;
        $cart->shipping = null;
        $cart->payment = array();
        $cart->save();
    }
    
    function onCartCreated($cart)
    {
        $customer = mm_getCustomer();
        if (!$customer) return;
        $cart->billing = $customer->billing;
        $cart->shipping = $customer->shipping;
        $cart->save();
    }
    
    /**
     * Copy billing and shipping information to customer's account.
     */
    function beforeCreateOrder($order)
    {
        if ($customer = mm_getCustomer()) {
            $customer->billing = $order->billing;
            $customer->shipping = $order->shipping;
            $customer->save();
            $order->customer = $customer;
        }
    }
    
}
