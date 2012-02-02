<?php
/**
 * @package paypal
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class paypal_TransactionDAOTest extends PHPUnit_Framework_TestCase
{
    private $generator;
    private $pp_generator;
    private $dao;
    
    function setUp()
    {
        $this->dao = new paypal_TransactionDAO;
        $this->pp_generator = new paypal_SampleGenerator;
        $this->generator = new order_SampleGenerator;
        $this->dao->deleteAll();
        $sdao = new sess_SessionDAO;
        $sdao->deleteAll();
    }
    
    function tearDown()
    {
    }
    
    function testAdd()
    {
        $trans = $this->pp_generator->makeTransaction();
        $this->dao->add($trans);
        $this->assertTrue($trans->id > 0, 'id should be > 0');
    }
    
    function testFetch()
    {
        $trans = $this->pp_generator->makeTransaction();
        $this->dao->add($trans);
        $actual = $this->dao->fetch($trans->id);
        $this->assertEquals($actual->id, $trans->id, 'transactions should match');
    }
    
    function testFetchByTxnId()
    {
        $trans = $this->pp_generator->makeTransaction();
        $this->dao->add($trans);
        $fetched = $this->dao->fetchByTxnId($trans->txn_id);
        $this->assertNotNull($fetched);
        $this->assertEquals($trans->id, $fetched->id);
    }
    
    function testFetchNoMatch()
    {
        $trans = $this->dao->fetch(0);
        $this->assertNull($trans);
        $trans = $this->dao->fetchByTxnId('abc');
        $this->assertNull($trans);
        $trans = $this->dao->fetchByCartId('abc');
        $this->assertNull($trans);
    }
    
    function testUpdate()
    {
        $trans = $this->pp_generator->makeTransaction();
        $this->dao->add($trans);
        $cart_id = 'abcd';
        $trans->order_id = 999;
        $this->dao->update($trans);
        $fetched = $this->dao->fetch($trans->id);
        $this->assertEquals($trans->id, $fetched->id);
    }
    
}
