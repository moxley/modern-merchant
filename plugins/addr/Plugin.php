<?php
/**
 * @package addr
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * Addresses
 * @package addr
 */
class addr_Plugin extends plugin_Base
{
    function info()
    {
        return array(
            'title'   => 'Address Management',
            'version' => '0.1',
            'author'  => 'Moxley Stratton',
            'url'     => 'http://www.modernmerchant.org/',
            'depends' => array()
        );
    }

    function install()
    {
        $db = mm_getDatabase();
        $drop = "DROP TABLE IF EXISTS mm_address";
        $db->execute($drop);
        
        $create = "CREATE TABLE mm_address (
            id          integer not null auto_increment,
            first_name  varchar(100),
            last_name   varchar(100),
            salutation   varchar(20),
            title       varchar(50),
            company     varchar(100),
            address_1   varchar(100),
            address_2   varchar(100),
            city        varchar(50),
            state       char(2),
            zip         char(10),
            country     char(2),
            phone_day   varchar(30),
            phone_night varchar(30),
            fax         varchar(30),
            email       varchar(100),
            PRIMARY KEY (id)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8";
        $db->execute($create);
        
        return true;
    }

    function upgrade_to_0_1()
    {
        $db = mm_getDatabase();
        $db->execute("DROP TABLE mm_address_seq");
        $db->execute("ALTER TABLE mm_address CONVERT TO CHARACTER SET utf8");
        $db->execute("ALTER TABLE mm_address CHANGE address_id id int NOT NULL auto_increment");
        $db->execute("ALTER TABLE mm_address CHANGE firstname first_name varchar(100)");
        $db->execute("ALTER TABLE mm_address CHANGE lastname last_name varchar(100)");
        $db->execute("ALTER TABLE mm_address ADD salutation varchar(20) AFTER last_name");
        $db->execute("ALTER TABLE mm_address CHANGE title title varchar(50)");
        $db->execute("ALTER TABLE mm_address CHANGE company company varchar(100)");
        $db->execute("ALTER TABLE mm_address CHANGE line1 address_1 varchar(100)");
        $db->execute("ALTER TABLE mm_address CHANGE line2 address_2 varchar(100)");
        $db->execute("ALTER TABLE mm_address CHANGE phone phone_day varchar(30)");
        $db->execute("ALTER TABLE mm_address CHANGE altphone phone_night varchar(30)");
        $db->execute("ALTER TABLE mm_address ADD country char(2)");
        $db->execute("ALTER TABLE mm_address ADD email varchar(100)");
        return true;
    }
}
