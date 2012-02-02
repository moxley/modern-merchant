<?php
/**
 * @package authnet
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

class authnet_Test extends PHPUnit_Framework_TestCase
{
    function setUp() {
        // Set up billing
        $this->billing = new addr_Address;
        $this->billing->first_name = 'Modern';
        $this->billing->street_address = '123 Main St.';
        
        $this->payment_values = array(
            'cc_name'      => 'Joan Doe',
            'cc_number'    => '1111222233334444',
            'cc_exp_month' => '02',
            'cc_exp_year'  => (string) (date('Y') + 1),
            'cc_cvv'       => '123'
        );
        
        // Set up cart
        $this->cart = new cart_Cart;
        $line = new cart_CartLine;
        $line->sku = '123';
        $line->price = '1.99';
        $line->qty = 1;
        $this->cart->lines[] = $line;
        $this->cart->billing = $this->billing;
        $this->cart->payment = $this->payment_values;
        $this->assertEquals('1111222233334444', $this->cart->payment['cc_number']);
        $this->assertTrue($this->cart->total > 0);
        
        // Set up payment module settings
        $this->settings = array(
            'account_id' => '1111'
        );
        $this->method = new authnet_AuthNet($this->settings);
        $this->method->cart = $this->cart;
        
        // Set up processor
        $this->processor = new authnet_AuthNetProcessor($this->method);
    }
    
    function testGetPayment() {
        $payment = $this->method->payment;
        $this->assertType('payment_CreditCardPayment', $payment);
        $this->assertEquals('1111222233334444', $payment->cc_number);
    }
    
    function testBuildRequestString() {
        $str = $this->processor->buildRequestString();
        $this->assertContains('x_login=' . urlencode($this->method->account_id), $str);
        $this->assertContains('x_amount=' . urlencode($this->cart->total), $str);
        $this->assertContains('x_first_name=' . urlencode($this->billing->first_name), $str);
        $this->assertContains('x_address=' . urlencode($this->billing->street_address), $str);
    }
    
    function testProcess() {
        $this->method->gateway_url = mm_getConfigValue('urls.https') . mm_getConfigValue('urls.plugins') . '/authnet/stub_gateway.php';
        $result = $this->processor->process();
        $this->assertTrue($result ? true : false);
        $this->assertTrue(strlen($this->processor->http_response) > 1, "Response string length");
    }
}

class authnet_DummyModel extends mvc_Model {
    function setBlah($str) {
        $this->_blah = $str;
    }
}
