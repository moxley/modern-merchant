<?php
/**
 * @package product
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * @package product
 */
class product_Plugin extends plugin_Base
{
    function info()
    {
        return array(
            'title'  => 'Product Management',
            'version' => '0.3',
            'author' => 'Moxley Stratton',
            'url'    => 'http://www.modernmerchant.org/',
            'depends' => array('mm', 'media'));
    }
    
    function install()
    {
        $db = mm_getDatabase();
        
        $drop = "DROP TABLE IF EXISTS `mm_product`";
        $db->execute($drop);
        
        $create = "CREATE TABLE `mm_product` (" .
                "`id`           int NOT NULL auto_increment," .
                "created_on     datetime default NULL," .
                "`modify_date`  datetime default NULL," .
                "available_on   datetime default NULL," .
                "`modify_user`  varchar(30) default NULL," .
                "`sku`          varchar(20) default NULL," .
                "`sortorder`    int not null default '0'," .
                "`name`         varchar(120) default NULL," .
                "`active`       int(1) NOT NULL default '0'," .
                "`description`  text," .
                "`comment`      text," .
                "`price`        decimal(6,2) default NULL," .
                "`count`        int default NULL," .
                "weight         decimal(8,3) default NULL," .
                "keywords       varchar(255) NOT NULL DEFAULT ''," .
                "PRIMARY KEY  (`id`)," .
                "UNIQUE (`sku`)," .
                "INDEX (sortorder)" .
                ") ENGINE=MyISAM DEFAULT CHARSET=utf8";
        $db->execute($create);
        
        mm_setSetting('images_per_product', 5);
        
        return true;
    }
    
    function upgrade_to_0_1()
    {
        $db = mm_getDatabase();
        
        $db->execute("DROP TABLE mm_product_seq");
        $db->execute("ALTER TABLE mm_product CONVERT TO CHARACTER SET utf8");
        $db->execute("ALTER TABLE mm_product CHANGE product_id id INT NOT NULL auto_increment");
        
        $db->execute("ALTER TABLE mm_product ADD created_on datetime AFTER id");
        $db->execute("ALTER TABLE mm_product ADD available_on datetime AFTER modify_date");
        $db->execute("ALTER TABLE mm_product MODIFY active int(1) NOT NULL DEFAULT '0'");
        $db->execute("ALTER TABLE mm_product MODIFY sku varchar(20) DEFAULT NULL");
        $indexes = new db_Indexes('mm_product');
        $indexes->dropForColumn('sku');
        $db->execute("ALTER TABLE mm_product ADD UNIQUE INDEX (sku)");
        
        $db->execute("ALTER TABLE mm_product CHANGE modify_date modify_date_int int(11) default null");
        $db->execute("ALTER TABLE mm_product ADD modify_date datetime");
        $db->execute("UPDATE mm_product SET modify_date=FROM_UNIXTIME(modify_date_int)");
        $db->execute("ALTER TABLE mm_product DROP modify_date_int");
        
        return true;
    }

    function upgrade_to_0_2() {
        $db = mm_getDatabase();
        $db->execute("ALTER TABLE mm_product ADD keywords varchar(255) NOT NULL DEFAULT ''");
        return true;
    }
    
    function upgrade_to_0_3()
    {
        $db = mm_getDatabase();
        $db->execute("ALTER TABLE mm_product ADD url_name varchar(255) NOT NULL");
        $res = $db->query("SELECT * FROM mm_product");
        while ($row = $res->fetchAssoc()) {
            $stmt = $db->prepare("UPDATE mm_product SET url_name=? WHERE id=?");
            $urlName = category_Category::generateUrlName($row['name']);
            $stmt->execute(array($urlName, $row['id']));
        }
        return true;
    }
}
