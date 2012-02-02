<?php
/**
 * @package user
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * @package user
 */
class user_Plugin extends plugin_Base
{
    function info()
    {
        return array(
            'title'   => "User Management",
            'version' => '0.2',
            'author'  => "Moxley Stratton",
            'url'     => "http://www.modernmerchant.org/",
            'depends' => array());
    }

    function install()
    {
        $db = mm_getDatabase();
        
        $drop  = "DROP TABLE IF EXISTS mm_user";
        $db->execute($drop);
        
        $create = "CREATE TABLE `mm_user` ( 
          `id`         int NOT NULL auto_increment,
          `username`   varchar(30) default NULL,
          `first_name` varchar(30) default NULL,
          `last_name`  varchar(40) default NULL,
          `password`   varchar(30) NOT NULL default '',
          `email` varchar(255),
          PRIMARY KEY  (`id`),
          UNIQUE KEY `username` (`username`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8";
        $db->execute($create);
        
        return true;
    }
    
    function uninstall()
    {
        $db = mm_getDatabase();
        
        $drop = "DROP TABLE IF EXISTS `mm_user`";
        $db->execute($drop);
        
        return true;
    }
    
    function upgrade_to_0_1()
    {
        $db = mm_getDatabase();
        
        $db->execute("DROP TABLE mm_user_seq");
        $db->execute("ALTER TABLE mm_user CONVERT TO CHARACTER SET utf8");
        $db->execute("ALTER TABLE mm_user CHANGE user_id id INT NOT NULL auto_increment");

        return true;
    }
    
    function upgrade_to_0_2()
    {
        $db = mm_getDatabase();
        return $db->execute("ALTER TABLE mm_user ADD email varchar(255)");
    }
}
