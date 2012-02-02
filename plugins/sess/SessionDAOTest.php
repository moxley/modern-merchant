<?php
/**
 * @package sess
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class sess_SessionDAOTest extends PHPUnit_Framework_TestCase
{
    private $sess;
    private $dao;
    
    function setUp()
    {
        $this->sess = new sess_Session;
        $this->dao = new sess_SessionDAO;
        $this->dao->deleteAll();
    }
    
    function tearDown()
    {
        $this->dao->deleteAll();
    }
    
    function testGet()
    {
        $value = 'modern merchant'; 
        $this->sess->set('test', $value);
        $actual = $this->sess->get('test');
        $this->assertEquals($value, $actual);
    }
    
    function testAdd()
    {
        $value = 'this is a test';
        $this->sess->set('test', $value);
        $this->dao->add($this->sess);
        $this->assertTrue($this->sess->id > 0, 'id should be > 0');
    }
    
    function testFetch()
    {
        $value = 'this is a test';
        $this->sess->set('test', $value);
        $this->dao->add($this->sess);
        $actual = $this->dao->fetch($this->sess->id);
        $this->assertEquals($this->sess, $actual);
    }
    
    function testUpdate()
    {
        $value = 'this is a test';
        $this->sess->set('test', $value);
        $this->dao->add($this->sess);
        $fetched = $this->dao->fetch($this->sess->id);
        $fetched->set('test', 'new value');
        $this->dao->update($fetched);
        $actual = $this->dao->fetch($this->sess->id);
        $this->assertEquals($fetched, $actual);
    }
    
    function testDeleteBySid()
    {
        $this->dao->add($this->sess);
        $fetched = $this->dao->fetch($this->sess->id);
        $this->assertNotNull($fetched);
        $this->dao->deleteBySid($this->sess->sid);
        $fetched = $this->dao->fetch($this->sess->id);
        $this->assertNull($fetched);
    }
    
    function testDeleteExpired()
    {
        $this->dao->deleteAll();
        $this->dao->add($this->sess);
        $maxlifetime = 60*60;
        $this->dao->deleteExpired($maxlifetime);
        
        $fetched = $this->dao->fetch($this->sess->id);
        $this->assertNotNull($fetched);
        
        $this->sess->modify_date -= $maxlifetime + 60;
        $this->dao->update($this->sess);
        $this->dao->deleteExpired($maxlifetime);
        $fetched = $this->dao->fetch($this->sess->id);
        $this->assertNull($fetched);
    }
    
    function testFetchBySid()
    {
        $this->dao->add($this->sess);
        $fetched = $this->dao->fetchBySid($this->sess->sid);
        $this->assertEquals($fetched, $this->sess);
    }
    
    function testFetchBySidNoData()
    {
        $this->sess->data = array();
        $this->dao->add($this->sess);
        $fetched = $this->dao->fetchBySid($this->sess->sid);
        $this->assertEquals($fetched, $this->sess);
    }
    
}
