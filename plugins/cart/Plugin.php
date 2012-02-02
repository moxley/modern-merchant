<?php
/**
 * @package cart
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * @package cart
 */
class cart_Plugin extends plugin_Base
{
    function info()
    {
        return array(
            'title'  => 'Shopping Cart',
            'version' => '0.3',
            'author' => 'Moxley Stratton',
            'url'    => 'http://www.modernmerchant.org/',
            'depends' => array('mm', 'content'));
    }
    
    function init()
    {
        mvc_Hooks::registerMenuItem(array('path' => 'admin/website/cart', 'action'=>'cart_admin.list', 'label' => 'Shopping Carts', 'priority' => 1));
    }
    
    function install()
    {
        $db = mm_getDatabase();

        $drop = "DROP TABLE IF EXISTS mm_cart";
        $db->execute($drop);
        
        $create = "CREATE TABLE mm_cart (
          id                  integer NOT NULL auto_increment,
          creation_date       datetime NOT NULL default '0000-00-00 00:00:00',
          sid                 varchar(128),
          order_id            integer,
          data                blob,
          PRIMARY KEY(id)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8";
        $db->execute($create);
        
        // Import email templates
        $dir = dirname(__FILE__) . '/emails';
        foreach (glob($dir . "/*.php") as $file) {
            $content = new content_Content;
            $sub_name = str_replace('.php', '', basename($file));
            $content->name = "order.email." . $sub_name;
            $content->type = 'php';
            $content->description = "Order Email: " . $sub_name;
            $content->body = file_get_contents($file);
            $content->save();
        }
        
        return true;
    }
    
    function upgrade_to_0_1()
    {
        $db = mm_getDatabase();

        $drop = "DROP TABLE IF EXISTS mm_cart";
        $db->execute($drop);
        
        $create = "CREATE TABLE mm_cart (
          id                  integer NOT NULL auto_increment,
          creation_date       datetime NOT NULL default '0000-00-00 00:00:00',
          sid                 varchar(128),
          order_id            integer,
          data                blob,
          PRIMARY KEY(id)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8";
        $db->execute($create);
        
        return true;
    }
    
    function upgrade_to_0_2() {
        // Import email templates
        $dir = dirname(__FILE__) . '/emails';
        foreach (glob($dir . "/*.php") as $file) {
            $content = new content_Content;
            $sub_name = str_replace('.php', '', basename($file));
            $content->name = "order.email." . $sub_name;
            $content->type = 'php';
            $content->description = "Order Email: " . $sub_name;
            $content->body = file_get_contents($file);
            $content->save();
        }

        return true;
    }
    
    function upgrade_to_0_3() {
        $dao = new content_ContentDAO;
        foreach(array('customer', 'sales') as $type) {
            $email_message = $dao->fetchByName('order.email.' . $type);
            $email_message->name = 'order.email.' . $type . '_old';
            $email_message->save();
        }

        $newTemplates = array('customer', 'sales', 'payment_notification_customer', 'payment_notification_sales');
        $dir = dirname(__FILE__) . '/emails';
        foreach ($newTemplates as $tplName) {
            $content = new content_Content;
            $content->name = "order.email." . $tplName;
            $content->type = 'php';
            $content->description = "Order Email: " . $tplName;
            $content->body = file_get_contents($dir . '/' . $tplName . '.php');
            $content->save();            
        }
        return true;
    }
}
