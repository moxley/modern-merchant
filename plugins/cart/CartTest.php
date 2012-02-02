<?php
/**
 * @package cart
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

if (!defined('MM_TEST_MODE')) {
    require_once dirname(__FILE__) . '/../../scripts/test.php';
}

/**
 * @package cart
 */
class cart_CartTest extends PHPUnit_Framework_TestCase
{
    private $generator;
    private $cart;
    
    function setUp()
    {
        $this->generator = new order_SampleGenerator;
        $this->cart = new cart_Cart;
    }
    
    function tearDown()
    {
    }
    
    function testAddProduct()
    {
        $product = $this->generator->makeProduct();
        $line = $this->cart->addProduct($product);
        $this->assertEquals(1, $this->cart->getLineCount(), 'line count');
        $this->assertTrue($line instanceof cart_CartLine, 'should return cart line');
        $this->assertEquals(1, $line->qty, 'qty');
        $this->assertEquals($product->sku, $line->sku, 'sku');
        $this->assertEquals($product->price, $line->price, 'price');
    }
    
    function testAddProductQtyTwo()
    {
        $product = $this->generator->makeProduct();
        $this->cart->addProduct($product);
        $this->cart->addProduct($product);
        $this->assertEquals(1, $this->cart->getLineCount(), 'line count');
    }
    
    function assertValidCart($cart)
    {
        $assert = new order_OrderAssertions($this);
        $assert->assertPopulatedCart($cart);
    }
    
    function testSetPayment()
    {
        $payment = array('cc_num' => '1111222233334444');
        $this->cart->setPropertyValues(array('payment' => $payment));
        $this->assertTrue(is_array($this->cart->payment));
        $this->assertEquals('1111222233334444', $this->cart->payment['cc_num']);
    }
    
    function testGetEmailMessage()
    {
        //$payment_method = new cart_DummyPaymentMethod;
        //$payment_method->save();
        //echo "name: " . $payment_method->name . "\n";
        //
        //$this->cart = $this->generator->makeCart();
        $this->cart = $this->generator->makeCart();
        $this->cart->getEmailMessage("customer.php");
    }
    
    function testProcessOrder()
    {
        $this->cart = $this->generator->makeCart();
        $this->assertTrue($this->cart->processOrder(), "Failed to process order: " . implode(', ', $this->cart->errors));
    }
    
    function testSetPaymentValues()
    {
        $payment_method = new payment_PaymentMethod;
        //$payment_method = new paypalwpp_DirectMethod;
        $payment_method->id = 3;
        $cart_values = array(
            'payment' => array(
                'cc_name'      => 'John Doe',
                'cc_type'      => 'Visa',
                'cc_number'    => '1111222233334444',
                'cc_exp_month' => '01',
                'cc_exp_year'  => (string) (date('Y') + 1),
                'cc_cvv'       => '123'
            )
        );
        $this->cart = new cart_Cart;
        $this->cart->payment_method = $payment_method;
        $this->cart->setPropertyValues($cart_values);
        $this->assertType('array', $this->cart->payment, "Payment should be an array");
        $this->assertEquals('123', $this->cart->payment['cc_cvv'], "Card CVV");

        $errors = $this->cart->validatePaymentMethod();
        $this->assertEquals(1, count($errors), "Errors: " . implode(',', $errors));
    }
}
