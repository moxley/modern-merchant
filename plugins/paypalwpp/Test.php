<?php
/**
 * @package paypalwpp
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

if (!defined('MM_TEST_MODE')) {
    require_once dirname(__FILE__) . '/../../scripts/test.php';
}

/**
 * @package paypalwpp
 */
class paypalwpp_Test extends PHPUnit_Framework_TestCase
{
    private $direct_method;
    private $cart;

    function setUp() {
        $this->environment = 'sandbox';
        payment_PaymentMethod::deleteAll();
        $this->cart = mm_getCart();
        $this->populateCart($this->cart);
    }
    
    function setUpDirectMethod() {
        $this->setUpMethod('direct');
        $this->direct_method->payment = $this->cart->payment;
        $this->assertTrue(is_object($this->direct_method->payment), "DirectMethod payment isn't an object: " . var_export($this->direct_method->payment, true));
    }
    
    function setUpExpressMethod() {
        $this->setUpMethod('express');
    }
    
    function setUpMethod($method) {
        $title = ucfirst($method);
        $class = "paypalwpp_" . ucfirst($method) . "Method";
        $obj_name = $method . '_method';
        $this->$obj_name = new $class;
        $this->$obj_name->settings->environment = 'sandbox';
        $this->$obj_name->settings->api_username['sandbox'] = paypalwpp_BaseMethod::TEST_API_USERNAME;
        $this->$obj_name->settings->api_password['sandbox'] = paypalwpp_BaseMethod::TEST_API_PASSWORD;
        $this->$obj_name->settings->api_signature['sandbox'] = paypalwpp_BaseMethod::TEST_API_SIGNATURE;

        $this->assertTrue($this->$obj_name->install(), "$title Method failed to install: " . implode(', ', $this->$obj_name->errors));
        
        $this->$obj_name->cart = $this->cart;
    }
    
    function testSaveFetchProperties() {
        // test 1
        $dao = new payment_PaymentMethodDAO;
        $dao->deleteAll();
        $method = new paypalwpp_DirectMethod;
        $method->sandbox->api_username = "test";
        $this->assertType('paypalwpp_Environment', $method->sandbox);
        $this->assertEquals('test', $method->sandbox->api_username);
        
        $this->assertTrue($method->save());
        $dao = new payment_PaymentMethodDAO;
        $fetched = $dao->fetch($method->id);
        $this->assertEquals('test', $fetched->sandbox->api_username);

        // test 2
        $method = new paypalwpp_DirectMethod;
        $values = array(
            'environment' => 'sandbox'
        );
        $method->setPropertyValues($values);
        $this->assertType('mvc_Model', $method);
        $this->assertEquals('sandbox', $method->environment);

        // test 3
        $method = new paypalwpp_DirectMethod;
        $values = array(
            'environment' => 'sandbox',
            'sandbox' => array(
                'api_username' => 'test'
            )
        );
        $method->setPropertyValues($values);
        $this->assertType('paypalwpp_Environment', $method->sandbox);
        $this->assertEquals('test', $method->sandbox->api_username);
    }
    
    function populateCart($cart) {
        $shipping_method = new shipping_ShippingMethod;
        $shipping_method->name = "Test Shipping Method";
        $shipping_method->cost = '10.00';
        $shipping_method->save();
        $cart->shipping_method = $shipping_method;
        
        $product_1 = new product_Product(array(
            'name' => 'Test Product 1', 'price' => '5.00', 'sku' => 'tp1', 'count' => 1, 'active' => true));
        $product_1->save();
        $cart->addProduct($product_1);

        $product_2 = new product_Product(array(
            'name' => 'Test Product 2', 'price' => '8.00', 'sku' => 'tp2', 'count' => 1, 'active' => true));
        $product_2->save();
        $cart->addProduct($product_2);
        
        $address_values = array(
            'first_name'   => 'Test',
            'last_name'    => 'Customer',
            'company'      => 'Modern Merchant',
            'address_1'    => '123 Main St.',
            'address_2'    => 'Suite 101',
            'city'         => 'Portland',
            'state'        => 'OR',
            'zip'          => '97212',
            'country'      => 'US',
            'phone_day'    => '503-555-1234',
            'phone_night'  => '503-555-4321',
            'email'        => 'customer1@moxleydata.com');
        $cart->billing = new cart_Billing($address_values);
        $cart->shipping = new cart_Shipping($address_values);
        $cart->shipping->shipping_method_id = $shipping_method->id;
        $payment = array(
            'cc_name'      => $cart->billing->name,
            'cc_type'      => 'Visa',
            'cc_number'    => '4147706547894046',
            'cc_exp_month' => '8',
            'cc_exp_year'  => date('Y') + 1,
            'cc_cvv'       => '917');
        $cart->payment = $payment;
    }
    
    function _testDirectMethod() {
        $this->setUpDirectMethod();
        //echo "DirectMethod object: " . var_export($this->direct_method, true) . "\n";
        //echo "cart: " . var_export($this->cart, true) . "\n";
        $this->assertTrue($this->direct_method->process($this->cart),
            "Processing failed: " . implode(', ', $this->direct_method->errors));
    }
    
    function _testSetExpressCheckout() {
        $this->setUpExpressMethod();
        $this->assertTrue($this->cart->total > 0, "Order total should be > 0");
        $token = $this->express_method->getNewToken($this->cart);
        $this->assertTrue(is_string($token) && strlen($token) > 0, "String length of token should be > 0");
    }
    
    function _testGetExpressCheckoutDetails() {
        $this->setUpExpressMethod();
        $token = $this->express_method->getNewToken($this->cart);
        $details = $this->express_method->getExpressCheckoutDetails($token);
        $this->assertTrue(is_object($details), "Should have returned an object: " . implode(', ', $this->express_method->errors));
    }
    
    //function testDoExpressCheckoutPayment() {
    //    $this->setUpExpressMethod();
    //    $token = $this->express_method->getNewToken($this->cart);
    //    $details = $this->express_method->getExpressCheckoutDetails($token);
    //    $result = $this->express_method->doExpressCheckoutPayment($token, $this->cart);
    //    $errors = $this->express_method->errors;
    //    $this->assertContains("not yet confirmed", $errors[0]);
    //}
}
