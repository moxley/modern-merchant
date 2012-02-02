<?php
/**
 * @package database
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class db_Test extends PHPUnit_Framework_TestCase
{
    function setUp() {
        $this->dbh = mm_getDatabase();
        $this->dbh->execute("drop table if exists mm_test");
        $this->dbh->execute("create table mm_test (" .
            "id int not null auto_increment," .
            "name varchar(255)," .
            "primary key (id)" .
            ")");
    }
    
    function testInsert() {
        $this->dbh->execute("insert into mm_test (name) values ('Modern Merchant')");
        $id = $this->dbh->lastInsertId();
        $this->assertEquals(1, $id, "ID should be 1");
        $count = $this->dbh->getOne("select count(*) from mm_test");
        $this->assertEquals(1, $count);
    }
    
    function testPreparedStatement() {
        $name = 'Modern Merchant';
        $this->dbh->execute("insert into mm_test (name) values (?)", array($name));
        $id = $this->dbh->lastInsertId();
        $rs = $this->dbh->query("select * from mm_test where name=?", array($name));
        $row = $rs->fetchAssoc();
        $rs->free();
        $this->assertEquals($id, $row['id']);
    }
    
    function testPreparedStatement2() {
        $db = mm_getDatabase();

        $sql = "UPDATE mm_session set data='blah?blah' where session_id=? and foo=? and bar=? and blah=2";
        $expected = "UPDATE mm_session set data='blah?blah' where session_id='1' and foo=1 and bar=NULL and blah=2";
        $stmt = new db_PreparedStatement($db, $sql);
        $stmt->params = array('1', 1, null);
        $sql2 = $stmt->interpolate();
        $this->assertEquals($expected, $sql2);
    }

    function testFetchRow() {
        $name = 'Modern Merchant';
        $this->dbh->execute("insert into mm_test (name) values (?)", array($name));
        $id = $this->dbh->lastInsertId();
        $rs = $this->dbh->query("select id,name from mm_test where name=?", array($name));
        $row = $rs->fetchRow();
        $rs->free();
        $this->assertEquals($id, $row[0]);
    }
}
