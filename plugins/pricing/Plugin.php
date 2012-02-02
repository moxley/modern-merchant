<?php
/**
 * @package pricing
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * @package pricing
 */
class pricing_Plugin extends plugin_Base
{
    function info()
    {
        return array(
            'title'  => 'Product Pricing',
            'version' => '0.1',
            'author' => 'Moxley Stratton',
            'url'    => 'http://www.modernmerchant.org/',
            'depends' => array());
    }
    
    function install()
    {
        $db = mm_getDatabase();
        
        $queries = array();
        $queries[] = "DROP TABLE IF EXISTS mm_pricing";
        $queries[] = "CREATE TABLE mm_pricing (" .
                "id int not null auto_increment," .
                "name varchar(30) default NULL," .
                "type enum('multiply', 'add', 'override') not null default 'multiply'," .
                "value decimal(6,4) not null default 0.0000," .
                "PRIMARY KEY (id)," .
                "UNIQUE KEY (name)" .
                ") ENGINE=MyISAM DEFAULT CHARSET=utf8";
        $queries[] = "DROP TABLE IF EXISTS mm_pricing_category";
        $queries[] = "CREATE TABLE mm_pricing_category (" .
                "id int not null auto_increment," .
                "pricing_id int not null," .
                "category_id int not null," .
                "PRIMARY KEY (id)," .
                "UNIQUE KEY (pricing_id, category_id)" .
                ") ENGINE=MyISAM DEFAULT CHARSET=utf8";
        
        foreach ($queries as $sql) {
            $db->execute($sql);
        }

        return true;
    }
    
    function uninstall()
    {
        $db = mm_getDatabase();
        
        $drop = "DROP TABLE IF EXISTS mm_pricing";
        $db->execute($drop);
        
        $drop = "DROP TABLE IF EXISTS mm_pricing_category";
        $db->execute($drop);
        
        return true;
    }
    
    function upgrade_to_0_1()
    {
        $db = mm_getDatabase();
        
        $db->execute("DROP TABLE mm_pricing_seq");
        $db->execute("ALTER TABLE mm_pricing CONVERT TO CHARACTER SET utf8");
        $db->execute("ALTER TABLE mm_pricing CHANGE pricing_id id INT NOT NULL auto_increment");
        $db->execute("ALTER TABLE mm_pricing MODIFY name varchar(30) default NULL");
        $db->execute("UPDATE mm_pricing SET name=CONCAT('pricing ', id) WHERE name=''");
        $db->execute("ALTER TABLE mm_pricing ADD UNIQUE INDEX (name)");

        $db->execute("DROP TABLE mm_pricing_category_seq");
        $db->execute("ALTER TABLE mm_pricing_category CHARACTER SET utf8");
        $db->execute("ALTER TABLE mm_pricing_category CHANGE pricing_category_id id INT NOT NULL auto_increment");
        
        return true;
    }
}
