<?php
/**
 * @package links
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * @package links
 */
class links_Plugin extends plugin_Base {
    function info() {
        return array(
            'title'        => "Links Exchange",
            'version'      => '0.0',
            'author'       => "Moxley Stratton",
            'url'          => "http://www.modernmerchant.org/",
            'depends'      => array(),
            'auto_install' => false);
    }
    
    function install() {
        $db = mm_getDatabase();
        $db->execute("DROP TABLE IF EXISTS mm_link");
        $create_sql = <<<EOF
        CREATE TABLE `mm_link` (
          `id` int(1) NOT NULL auto_increment,
          `category_id` int(1) NOT NULL default '0',
          `created_on` datetime,
          `url` varchar(255) NOT NULL default '',
          `email` varchar(128) default NULL,
          `description` text,
          `comment` text,
          `business_name` varchar(255) default NULL,
          `approved` tinyint(1) default NULL,
          `reciprocal_url` varchar(255) default NULL,
          `counter` int(3) default NULL,
          PRIMARY KEY  (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8
EOF;
        $db->execute($create_sql);
        
        $db->execute("DROP TABLE IF EXISTS mm_link_category");
        $create_sql = <<<EOF
            CREATE TABLE `mm_link_category` (
                `id` int(11) NOT NULL auto_increment,
                `name` varchar(120) default NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8
EOF;
        $db->execute($create_sql);
        
        return true;
    }
    
    function uninstall() {
        $db = mm_getDatabase();
        $db->execute("DROP TABLE IF EXISTS mm_link");
        $db->execute("DROP TABLE IF EXISTS mm_link_category");
        return true;
    }
    
    function init() {
        mvc_Hooks::registerMenuItem(array('path' => 'admin/website/links', 'action' => 'links_admin', 'label' => 'Links'));
    }
}
