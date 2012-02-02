<?php
/**
 * @package authnet
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

if (!defined('MM_TEST_MODE')) {
    require_once dirname(__FILE__) . '/../../scripts/test.php';
}

/**
 * @package authnet
 */
class authnet_AuthNetTest extends PHPUnit_Framework_TestCase
{
    function setUp() {
        $this->authnet = new authnet_AuthNet;
        $this->authnet->dao->deleteAll();
        $this->generator = new order_SampleGenerator;
    }
    
    function testInstall() {
        $this->authnet->install();
        $this->assertTrue($this->authnet->id > 0);
        $this->assertEquals('authnetuser', $this->authnet->account_id);
        $this->assertEquals("Credit Card", $this->authnet->public_title);
        $fetched = $this->authnet->dao->fetch($this->authnet->id);
        $this->assertEquals('authnetuser', $fetched->account_id);
        $this->assertEquals("Credit Card", $fetched->public_title);
    }

    function testSave() {
        $this->authnet->account_id = 'abc123';
        $this->assertEquals('abc123', $this->authnet->account_id);
        $this->authnet->save();
        $fetched = $this->authnet->dao->fetch($this->authnet->id);
        $this->assertTrue($fetched ? true : false);
        $this->assertEquals('abc123', $fetched->account_id, "Should have account_id");
    }
    
    function testProcess()
    {
        $this->cart = $this->generator->makeCart();
        $this->cart->payment_method = $this->authnet;
        $this->authnet->process($this->cart);
    }
}
