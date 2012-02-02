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
class access_TestVersion02 extends PHPUnit_Framework_TestCase
{
    function setUp() {
        $this->plugin = new access_Plugin;
    }
    
    function tearDown() {
        $this->dropTables();
        $this->createNewTables();
    }
    
    function assert($condition, $msg="") {
        $this->assertTrue($condition ? true : false, $msg);
    }
    
    function dropTables() {
        $db = mm_getDatabase();
        $db->execute("DROP TABLE IF EXISTS `mm_access`");
        $db->execute("DROP TABLE IF EXISTS `mm_user_access`");
    }
    
    function createOldTable() {
        $db = mm_getDatabase();
        $db->execute("CREATE TABLE `mm_access` (
          `id` int(11) NOT NULL auto_increment,
          `name` varchar(50) default NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `name` (`name`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8");
    }
    
    function createNewTables() {
        $db = mm_getDatabase();
        $db->execute("DROP TABLE IF EXISTS mm_access");
        $db->execute("CREATE TABLE mm_access (" .
            "id    int(11) not null auto_increment," .
            "name  varchar(50)," .
            "title varchar(255)," .
            "PRIMARY KEY (id)," .
            "UNIQUE KEY (name)" .
            ") ENGINE=MyISAM DEFAULT CHARSET=utf8");
        
        $db->execute("DROP TABLE IF EXISTS mm_user_access");
        $db->execute("CREATE TABLE mm_user_access (" .
            "id        int(11) not null auto_increment," .
            "access_id int(11) not null," .
            "user_id   int(11) not null," .
            "PRIMARY KEY (id)," .
            "UNIQUE KEY (access_id, user_id)" .
            ") ENGINE=MyISAM DEFAULT CHARSET=utf8");
    }
    
    function insertOldRecords() {
        $db = mm_getDatabase();
        $db->execute("INSERT INTO `mm_access` VALUES (1, 'read-only')");
        $db->execute("INSERT INTO `mm_access` VALUES (2, 'Modern Merchant Manager Interface')");
        $db->execute("DELETE FROM mm_user");
        $db->execute("INSERT INTO `mm_user`   VALUES (1, 'admin',NULL,NULL,'2','admin')");
        $db->execute("INSERT INTO `mm_user`   VALUES (2, 'customer',NULL,NULL,'3','customer')");
    }
    
    function testInstallFromScratch() {
        $this->dropTables();
        $this->assert($this->plugin->install());
        $this->assertInstalled();
    }
    
    function testUpgrade() {
        $this->dropTables();
        $this->createOldTable();
        $this->insertOldRecords();
        $this->assert($this->plugin->upgrade_to_0_2());
        $this->assertInstalled();
        
        // Assert upgraded
        $user = user_User::fetchByUsername('admin');
        $names = $user->access_names;
        $this->assertEquals(3, count($names));
        $expected_names = array('admin.read', 'admin.write', 'user.management');
        foreach ($names as $i=>$name) {
            $pos = array_search($name, $expected_names);
            $this->assertTrue($pos !== false, "Failed to find '$name' in access_names");
            unset($expected_names[$pos]);
        }
    }
    
    function assertInstalled() {
        $db = mm_getDatabase();
        $rs = $db->query("SELECT * FROM mm_access");
        $this->access_id_to_row = array();
        $name_to_row = array();
        while ($row = $rs->fetchAssoc()) {
            $name_to_row[$row['name']] = $row;
            $this->access_id_to_name[$row['id']] = $row['name'];
        }
        $this->assertEquals(4, count($name_to_row));
        $this->assert(isset($name_to_row['admin.read']));
        $this->assertEquals("read-only", $name_to_row['admin.read']['title']);
        $this->assert(isset($name_to_row['admin.write']));
        $this->assertEquals("Modern Merchant Manager Interface", $name_to_row['admin.write']['title']);
        $this->assert(isset($name_to_row['user.management']));
        $this->assertEquals("User Management", $name_to_row['user.management']['title']);
    }
}
