<?php
/**
 * @package payment
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * @package payment
 */
class payment_Plugin  extends plugin_Base
{
    function info()
    {
        return array(
            'title'  => 'Payments Support',
            'version' => '0.1',
            'author' => 'Moxley Stratton',
            'url'    => 'http://www.modernmerchant.org/',
            'depends' => array('mm', 'cart'));
    }
    
    function install()
    {
        $db = mm_getDatabase();
        
        $drop = "DROP TABLE IF EXISTS `mm_payment_method`";
        $db->execute($drop);
        
        $create = "CREATE TABLE `mm_payment_method` (
          id int NOT NULL auto_increment,
          name varchar(30) default NULL,
          active int(1) NOT NULL default '0',
          class  varchar(50) default NULL,
          sortorder  int NOT NULL default '0',
          public_title varchar(60) default '',
          settings text,
          PRIMARY KEY  (id),
          UNIQUE INDEX (`name`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8";
        $db->execute($create);
        
        mm_setSetting('default_payment_method', 0);

        return true;
    }
    
    function upgrade_to_0_1()
    {
        $db = mm_getDatabase();
        $db->execute("DROP TABLE IF EXISTS mm_payment_method_seq");
        $db->execute("ALTER TABLE mm_payment_method CONVERT TO CHARACTER SET utf8");
        $db->execute("ALTER TABLE mm_payment_method CHANGE payment_method_id id int NOT NULL auto_increment");
        $db->execute("ALTER TABLE mm_payment_method DROP title");
        $db->execute("ALTER TABLE mm_payment_method ADD public_title varchar(60) default ''");
        $db->execute("ALTER TABLE mm_payment_method ADD settings text");
        $db->execute("ALTER TABLE mm_payment_method MODIFY active int(1) not null default '0'");
        $db->execute("UPDATE mm_payment_method SET class='authnet_AuthNet' WHERE class='model_AuthNet'");
        $db->execute("UPDATE mm_payment_method SET class='paypal_PayPal' WHERE class='model_PayPal'");
        
        return true;
    }
}
