<?php
/**
 * @package shipping
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * @package shipping
 */
class shipping_Plugin extends plugin_Base
{
    function info()
    {
        return array(
            'title'  => 'Shipping Methods',
            'version' => '0.1',
            'author' => 'Moxley Stratton',
            'url'    => 'http://www.modernmerchant.org/',
            'depends' => array());
    }

    function install()
    {
        $db = mm_getDatabase();
        
        $queries = array();
        $queries[] = "DROP TABLE IF EXISTS `mm_shipping_method`";
        $queries[] = "CREATE TABLE `mm_shipping_method` (
          id int NOT NULL auto_increment,
          name   varchar(30) default NULL,
          active int(1) NOT NULL default '0',
          cost   decimal(6,2) default NULL,
          calc   text,
          sortorder integer not null default '0',
          PRIMARY KEY  (`id`),
          index (sortorder)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8";

        $queries[] = "INSERT INTO `mm_shipping_method` VALUES (1,'Free Shipping',1,15.00,'// Return \$1, plus .25% of sub total\r\nreturn 0.00 + \$cart->getSubTotal() * 0.00;', 0)";
        $queries[] = "INSERT INTO `mm_shipping_method` VALUES (3,'USPS International Insured',0,20.00,'return 25.00;', 0)";
        $queries[] = "INSERT INTO `mm_shipping_method` VALUES (4,'USPS International Priority',0,5.00,'return 5.00;', 0)";
        $queries[] = "INSERT INTO `mm_shipping_method` VALUES (5,'USPS Priority',1,NULL,'return 4.00 + intval(\$cart->getSubTotal() / 10) * 0.75;', 0)";

        foreach ($queries as $query) {
            $db->execute($query);
        }
        
        mm_setSetting('default_shipping_method', 1);
        
        return true;
    }
    
    function upgrade_to_0_1()
    {
        $db = mm_getDatabase();
        
        $db->execute("DROP TABLE mm_shipping_method_seq");
        $db->execute("ALTER TABLE mm_shipping_method CONVERT TO CHARACTER SET utf8");
        $db->execute("ALTER TABLE mm_shipping_method CHANGE shipping_method_id id INT NOT NULL auto_increment");
        
        $db->execute("ALTER TABLE mm_shipping_method MODIFY cost decimal(6,2) default NULL");

        return true;
    }
}
