<?php
/**
 * @package sess
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * @package sess
 */
class sess_Plugin extends plugin_Base
{
    function info()
    {
        return array(
            'title'  => 'Sessions',
            'version' => '0.1',
            'author' => 'Moxley Stratton',
            'url'    => 'http://www.modernmerchant.org/',
            'depends' => array('db'));
    }
    
    function install()
    {
        $db = mm_getDatabase();
        
        $drop = "DROP TABLE IF EXISTS `mm_session`";
        $db->execute($drop);
        
        $create = "CREATE TABLE `mm_session` (" .
                "`id`    int NOT NULL auto_increment," .
                  "`sid`           varchar(50) default NULL," .
                  "`creation_date` datetime default NULL," .
                  "`modify_date`   datetime default NULL," .
                  "`data`          mediumblob," .
                  "PRIMARY KEY  (id)," .
                  "UNIQUE KEY (sid)" .
                  ") ENGINE=MyISAM DEFAULT CHARSET=utf8";
        $db->execute($create);

        return true;
    }
    
    function init()
    {
        mvc_Hooks::registerMenuItem(array('path' => 'admin/website/sessions', 'action'=>'sess_admin.list', 'label' => 'Sessions', 'priority' => 1));
    }

    function upgrade_to_0_1()
    {
        $db = mm_getDatabase();
        
        $db->execute("DROP TABLE mm_session_seq");
        $db->execute("ALTER TABLE mm_session CONVERT TO CHARACTER SET utf8");
        $db->execute("ALTER TABLE mm_session CHANGE session_id id INT NOT NULL auto_increment");
        $db->execute("ALTER TABLE mm_session ADD modify_date datetime AFTER creation_date");
        $db->execute("UPDATE mm_session SET modify_date=creation_date");
        $rows = $db->getAllAssoc("SELECT s1.* FROM mm_session s1 LEFT JOIN mm_session s2 ON s2.sid = s1.sid AND s2.id != s1.ID WHERE s2.id > 0");
        foreach ($rows as $row) {
            $db->execute("DELETE FROM mm_session WHERE id=?", array($row['id']));
        }
        $indexes = new db_Indexes('mm_session');
        $indexes->dropForColumn('sid');
        $db->execute("ALTER TABLE mm_session ADD UNIQUE KEY (sid)");
        
        return true;
    }
}
