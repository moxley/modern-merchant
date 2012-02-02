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
class cart_Test extends PHPUnit_Framework_TestCase
{
    function setUp() {
        $dao = new payment_PaymentMethodDAO;
        $dao->deleteAll();
    }
    
    function testPopulateCartLine() {
        $values = array(
            'id' => 5,
            'sku' => 'abc',
            'description' => 'testline',
            'qty' => 2,
            'price' => '5.00'
        );
        $line = new cart_CartLine($values);
        $this->assertEquals(5, $line->id);
        $this->assertEquals('abc', $line->sku);
        $this->assertEquals('testline', $line->description);
        $this->assertEquals(2, $line->qty);
        $this->assertEquals('5.00', $line->price);
    }
    
    function testPopulateCart() {
        $line_values = array(
            'id' => 5,
            'sku' => 'abc',
            'description' => 'testline',
            'qty' => 2,
            'price' => '5.00'
        );
        $cart_values = array(
            'lines' => array($line_values),
            'order_id' => 1
        );
        $cart = new cart_Cart($cart_values);
        $this->assertTrue(is_array($cart->lines), "lines should be an array");
        $this->assertEquals(1, $cart->order_id, 'order_id');
        $this->assertEquals(1, count($cart->lines), "line count");
    }
    
    function testPopulateCart2() {
        $req = array (
            'action' => 'cart.shippingPage',
            'cart' => array (
                'shipping' =>
                array (
                    'first_name' => 'Modern'
                )
            )
        );
        $cart = new cart_Cart;
        $this->assertTrue($cart->shipping->is_empty, "shipping should be empty");
        $cart->setPropertyValues($req['cart']);
        $this->assertFalse($cart->shipping->is_empty, "shipping should not be empty");
        $this->assertEquals('Modern', $cart->shipping->first_name, 'first_name');
    }
    
    function testSetPropertyValues() {
        $req = array (
            'action' => 'cart.shippingPage',
            'cart' => array (
                'payment_method_id' => 1,
                'payment' => array (
                    'cc_exp_month' => '02'
                )
            )
        );
        //$db = mm_getDatabase();
        //$db->execute("DELETE FROM mm_payment_method");
        
        $payment_method = new cart_DummyPaymentMethod;
        $payment_method->active = true;
        $payment_method->class = get_class($payment_method);
        $payment_method->save();
        $this->assertTrue($payment_method->id > 0, "id should be > 0");
        $req['cart']['payment_method_id'] = $payment_method->id;
        
        $cart = new cart_Cart;
        $cart->setPropertyValues($req['cart']);
        $this->assertTrue(is_array($cart->payment), "Cart payment should be an array. Was: " . var_export($cart->payment, true));
        $this->assertEquals('02', $cart->payment['cc_exp_month'], "Expiration month");
        
        $req = array (
            'action' => 'cart.shippingPage',
            'cart' => array (
                'payment_method_id' => (string) $payment_method->id,
                'payment' => array (
                    'cc_exp_month' => '05'
                )
            )
        );
        $cart->setPropertyValues($req['cart']);
        $this->assertEquals('05', $cart->payment['cc_exp_month'], "Expiration month");
    }
    
    /**
     * Test to make sure errors get propagated up the Model chain (product -> cart_line -> cart).
     */
    function testNonActiveProduct() {
        $this->cart = new cart_Cart;
        $this->product = new product_Product(array('sku_same_as_id' => true, 'name' => "Test Product", 'active' => true, 'count' => 10, 'price' => '10.00', 'modify_username' => 'testuser'));
        $this->assertTrue($this->product->save());

        $this->assertTrue((boolean) $this->cart->addProduct($this->product), "Should have added the product: " . implode(', ', $this->cart->errors));
        $this->assertEquals(1, count($this->cart->lines), "Number of lines");
        
        $this->product->active = false;
        $this->assertTrue($this->product->save());
        $this->assertFalse((boolean) $this->cart->addProduct($this->product), "Should not have added the non-active product");
        $this->assertEquals(1, count($this->cart->lines), "Number of lines");

        $this->cart = new cart_Cart;
        $this->assertFalse((boolean) $this->cart->addProduct($this->product), "Should not have added the non-active product");
        $this->assertEquals(0, count($this->cart->lines), "Number of lines");
    }
    
    function testCartLinesPersist()
    {
        $this->generator = new order_SampleGenerator;
        $this->cart = $this->generator->makeCart();
        mm_setCart($this->cart);
        $line = $this->cart->lines[0];

        $this->assertTrue(!empty($this->cart->lines), "Cart should have lines");
        
        $cart2 = mvc_Model::fetch('cart_Cart', $this->cart->id);
        $this->assertTrue(!empty($cart2->lines), "Cart should have lines");
        
    }
}

class cart_DummyPayment extends payment_CreditCardPayment {
    
}
