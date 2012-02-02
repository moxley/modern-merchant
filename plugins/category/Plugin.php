<?php
/**
 * @package category
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * @package category
 */
class category_Plugin extends plugin_Base
{
    function info()
    {
        return array(
            'title'    => "Product Categories",
            'version'  => '0.4',
            'author'   => "Moxley Stratton",
            'url'      => 'http://www.modernmerchant.org/',
            'depends'  => array('product', 'media')
        );
    }
    
    function install()
    {
        $db = mm_getDatabase();
                
        $drop = "DROP TABLE IF EXISTS `mm_product_category`";
        $db->execute($drop);

        $create = "CREATE TABLE `mm_product_category` (" .
                "  id            integer not null auto_increment," .
                "  `category_id` INT NOT NULL," .
                "  `product_id`  INT NOT NULL," .
                "  `sortorder`   INT NOT NULL default '0'," .
                "  PRIMARY KEY (id)," .
                "  UNIQUE KEY  (`product_id`, `category_id`)," .
                "  INDEX (sortorder)" .
                ") ENGINE=MyISAM DEFAULT CHARSET=utf8";
        $db->execute($create);
        
        $dao = new category_CategoryDAO;
        $dao->createCategoryTable();
        
        return true;
    }
    
    function upgrade_to_0_1()
    {
        $db = mm_getDatabase();
        $db->execute("DROP TABLE mm_category_seq");
        $db->execute("ALTER TABLE mm_category CONVERT TO CHARACTER SET utf8");
        $db->execute("ALTER TABLE mm_category CHANGE category_id id INT NOT NULL auto_increment");
        $db->execute("ALTER TABLE mm_category MODIFY parent_id int NOT NULL default '0'");
        $db->execute("ALTER TABLE mm_category MODIFY sortorder int NOT NULL default '0'");

        $db->execute("DROP TABLE mm_product_category_seq");
        $db->execute("ALTER TABLE mm_product_category CHARACTER SET utf8");
        $table = $db->getOneAssoc("describe mm_product_category");
        $has_id = false;
        foreach ($table as $col) {
            if ($col['Field'] == 'product_category_id') {
                $has_id = true;
                break;
            }
        }
        if ($has_id) {
            $db->execute("ALTER TABLE mm_product_category CHANGE product_category_id id INT NOT NULL auto_increment");
        }
        else {
            $db->execute("ALTER TABLE mm_product_category DROP primary key");
            $db->execute("ALTER TABLE mm_product_category ADD id INT NOT NULL auto_increment PRIMARY KEY FIRST");
        }
        
        return true;
    }
    
    function upgrade_to_0_2() {
        $db = mm_getDatabase();
        $db->execute("ALTER TABLE mm_category DROP writable");
        $db->execute("ALTER TABLE mm_category ADD keywords varchar(255)");
        return true;
    }
    
    function upgrade_to_0_3() {
        $db = mm_getDatabase();
        $db->execute("ALTER TABLE mm_product_category DROP index category_id");
        return $db->execute("ALTER TABLE mm_product_category ADD index (product_id, category_id)");
    }
    
    function upgrade_to_0_4() {
        $db = mm_getDatabase();
        
        // url_name
        $db->execute("ALTER TABLE mm_category ADD url_name varchar(255) AFTER name");
        $db->execute("ALTER TABLE mm_category ADD INDEX (url_name)");
        $rs = $db->query("SELECT name,id FROM mm_category");
        while ($row = $rs->fetchAssoc()) {
            $url_name = category_Category::generateUrlName($row['name']);
            $db->execute("UPDATE mm_category SET url_name=? WHERE id=?", array($url_name, $row['id']));
        }
        
        // left, right
        $db->execute("ALTER TABLE mm_category ADD lft int NOT NULL AFTER id");
        $db->execute("ALTER TABLE mm_category ADD rgt int NOT NULL AFTER lft");
        $db->execute("ALTER TABLE mm_category ADD INDEX (lft)");
        $db->execute("ALTER TABLE mm_category ADD INDEX (rgt)");
        $db->execute("UPDATE mm_category SET lft=sortorder WHERE id != 0");
        
        $dao = new category_CategoryDAO;
        $dao->fixHierarchy();
        
        return true;
    }
}
