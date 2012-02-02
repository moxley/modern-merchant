<?php
/**
 * @package access
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * @package access
 */
class access_Plugin extends plugin_Base
{
    function info()
    {
        return array(
            'title'   => 'Access Types',
            'version' => '0.2',
            'author'  => 'Moxley Stratton',
            'url'     => 'http://www.modernmerchant.org/',
            'depends' => array()
        );
    }
    
    function install()
    {
        $db = mm_getDatabase();
        $queries = array();
        $queries[] = "DROP TABLE IF EXISTS mm_access";
        $queries[] = "CREATE TABLE mm_access (" .
            "id    int(11) not null auto_increment," .
            "name  varchar(50)," .
            "title varchar(255)," .
            "PRIMARY KEY (id)," .
            "UNIQUE KEY (name)" .
            ") ENGINE=MyISAM DEFAULT CHARSET=utf8";
        $queries[] = "INSERT INTO mm_access VALUES (1, 'admin.read', 'Admin Read')";
        $queries[] = "INSERT INTO mm_access VALUES (2, 'admin.write', 'Admin Read/Write')";
        $queries[] = "INSERT INTO mm_access VALUES (3, 'customer', 'Customer')";
        $queries[] = "INSERT INTO mm_access VALUES (4, 'user.management', 'User Management')";
        $queries[] = "DROP TABLE IF EXISTS `mm_user_access`";
        $queries[] = "CREATE TABLE mm_user_access (" .
            "id        int(11) not null auto_increment," .
            "access_id int(11) not null," .
            "user_id   int(11) not null," .
            "PRIMARY KEY (id)," .
            "UNIQUE KEY (access_id, user_id)" .
            ") ENGINE=MyISAM DEFAULT CHARSET=utf8";
        foreach ($queries as $query) {
            $db->execute($query);
        }
        return true;
    }
    
    function uninstall()
    {
        $db = mm_getDatabase();
        $drop = "DROP TABLE IF EXISTS mm_access";
        $db->execute($drop);
    }
    
    function upgrade_to_0_1()
    {
        $db = mm_getDatabase();
        $db->execute("DROP TABLE mm_access_seq");
        $db->execute("ALTER TABLE mm_access CONVERT TO CHARACTER SET utf8");
        $db->execute("ALTER TABLE mm_access CHANGE access_id id int NOT NULL auto_increment");
        return true;
    }
    
    function upgrade_to_0_2()
    {
        $db = mm_getDatabase();
        
        // Add "mm_access.title", copy "name" values to "title", update "name" with new values
        $db->execute("ALTER TABLE mm_access ADD title varchar(255)");
        $db->execute("UPDATE mm_access SET title=name");
        $db->execute("UPDATE mm_access SET name='admin.read', title='Admin Read' WHERE id=1");
        $db->execute("UPDATE mm_access SET name='admin.write', title='Admin Read/Write' WHERE id=2");
        
        // Add "User Management" mm_access record
        $db->execute("INSERT INTO mm_access (id, name, title) VALUES (3, 'customer', 'Customer')");
        $db->execute("INSERT INTO mm_access (id, name, title) VALUES (4, 'user.management', 'User Management')");

        // Create table mm_user_access
        $db->execute("DROP TABLE IF EXISTS mm_user_access");
        $db->execute("CREATE TABLE mm_user_access (" .
            "id        int(11) not null auto_increment," .
            "access_id int(11) not null," .
            "user_id   int(11) not null," .
            "PRIMARY KEY (id)," .
            "UNIQUE KEY (access_id, user_id)" .
            ") ENGINE=MyISAM DEFAULT CHARSET=utf8");
        
        $name_to_id = array();
        $rs = $db->query("SELECT * FROM mm_access");
        while ($row = $rs->fetchAssoc()) {
            $name_to_id[$row['name']] = $row['id'];
        }
        $rs->free();
        
        // Add mm_user_access records for every user's 'type' and 'id' columns, and a record for the existing access types
        $rs = $db->query("SELECT * FROM mm_user");
        while ($row = $rs->fetchAssoc()) {
            if ($row['type'] == '3') {
                $db->execute("INSERT INTO mm_user_access (user_id, access_id) VALUES (?,?)", array($row['id'], $name_to_id['customer']));
            }
            else {
                $db->execute("INSERT INTO mm_user_access (user_id, access_id) VALUES (?,?)", array($row['id'], $row['type']));
                $db->execute("INSERT INTO mm_user_access (user_id, access_id) VALUES (?,?)", array($row['id'], $name_to_id['admin.read']));
                $db->execute("INSERT INTO mm_user_access (user_id, access_id) VALUES (?,?)", array($row['id'], $name_to_id['user.management']));
            }
        }
        $rs->free();
        
        $db->execute("ALTER TABLE mm_user DROP type");
        
        return true;
    }
    
}
