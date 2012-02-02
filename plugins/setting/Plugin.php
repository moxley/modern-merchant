<?php
/**
 * @package setting
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * @package setting
 */
class setting_Plugin extends plugin_Base
{
    function info()
    {
        return array(
            'title'    => "Database-Stored Settings",
            'version'  => '0.1',
            'author'   => "Moxley Straton",
            'url'      => 'http://www.modernmerchant.org/',
            'depends'  => array('db')
        );
    }
    
    function install()
    {
        $db = mm_getDatabase();
        
        $drop = "DROP TABLE IF EXISTS `mm_setting`";
        $db->execute($drop);
        
        $create = "CREATE TABLE `mm_setting` (
          id          int not null auto_increment,
          name        varchar(255) NOT NULL default '',
          value       text,
          description text,
          sortorder   integer not null default '0',
          type        enum('string', 'boolean', 'integer', 'float') not null default 'string',
          PRIMARY KEY (id),
          UNIQUE KEY (name),
          INDEX (sortorder)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8";
        $db->execute($create);
        
        setting_SettingDAO::clearCache();
        
        return true;
    }

    function upgrade_to_0_1()
    {
        $db = mm_getDatabase();
        
        setting_SettingDAO::clearCache();
        
        $db->execute("DROP TABLE mm_setting_seq");
        $db->execute("ALTER TABLE mm_setting CONVERT TO CHARACTER SET utf8");
        $db->execute("ALTER TABLE mm_setting CHANGE setting_id id INT NOT NULL auto_increment");
        $db->execute("ALTER TABLE mm_setting DROP category_id");
        $db->execute("ALTER TABLE mm_setting DROP perms");
        $db->execute("ALTER TABLE mm_setting DROP owner");
        $db->execute("ALTER TABLE mm_setting DROP _group");

        return true;
    }
}
