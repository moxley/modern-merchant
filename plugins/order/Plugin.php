<?php
/**
 * @package order
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * @package order
 */
class order_Plugin extends plugin_Base
{
    function info()
    {
        return array(
            'title'   => "Order Management",
            'version' => '0.1',
            'author'  => "Moxley Stratton",
            'url'     => "http://www.modernmerchant.org/",
            'depends' => array('mm'));
    }

    function install()
    {
        $db = mm_getDatabase();
        
        $drop = "DROP TABLE IF EXISTS mm_order";
        $db->execute($drop);
        
        $create = "CREATE TABLE mm_order (
          `id`                  integer NOT NULL auto_increment,
          `order_date`          datetime default NULL,
          `creation_date`       datetime NOT NULL default '0000-00-00 00:00:00',
          `modify_user`         varchar(30) default NULL,
          `sub_total`            decimal(6,2) default NULL,
          `ship_total`          decimal(6,2) default NULL,
          `ship_date`           datetime default NULL,
          `shipping_method_id`  int default NULL,
          `payment_method_id`   int NOT NULL default '0',
          `tracking`            varchar(30) default NULL,
          `total`               decimal(6,2) default NULL,
          `cust_approved`       enum('T','F') NOT NULL default 'F',
          `payed`               enum('T','F') NOT NULL default 'F',
          `unique_code`         varchar(127) default NULL,
          `cart_id`             integer,
          `session_id`          varchar(100) NOT NULL default '',
          customer_id           integer,
          billing_address_id int,
          shipping_address_id int,
          `data`                blob,
          `notes`               text,
          PRIMARY KEY  (`id`),
          INDEX (customer_id)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8";
        $db->execute($create);
        
        mm_setSetting('orders.notification', 'example@example.com');
        mm_setSetting('sales.notify', 'example@example.com');
        
        return true;
    }
    
    function upgrade_to_0_1()
    {
        $db = mm_getDatabase();
        $db->execute("DROP TABLE mm_order_seq");

        $db->execute("ALTER TABLE mm_order CONVERT TO CHARACTER SET utf8");
        $db->execute("ALTER TABLE mm_order CHANGE order_id id INT NOT NULL auto_increment");
        $db->execute("ALTER TABLE mm_order ADD cart_id integer AFTER unique_code");
        $db->execute("ALTER TABLE mm_order ADD customer_id integer AFTER session_id");
        $db->execute("ALTER TABLE mm_order ADD INDEX (customer_id)");
        
        return true;
    }
}
