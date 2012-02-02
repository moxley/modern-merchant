<?php
/**
 * @package paypal
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

if (!defined('MM_TEST_MODE')) {
    require_once dirname(__FILE__) . '/../../scripts/test.php';
}

/**
 * Tests the paypal (IPN) payment method.
 * @package paypal
 */
class paypal_PayPalTest extends PHPUnit_Framework_TestCase
{
    function setUp() {
        $this->paypal = new paypal_PayPal;
        $this->paypal->dao->deleteAll();
        $this->paypal->install();
    }
    
    function testInstall() {
        $this->assertTrue($this->paypal->id > 0);
        $fetched = $this->paypal->dao->fetch($this->paypal->id);
        $this->assertTrue($fetched?true:false);
        $this->assertEquals('example@example.com', $fetched->account_email);
    }
    
    function testGetAccountEmail() {
        $this->paypal->account_email = 'abc@example.com';
        $this->assertEquals('abc@example.com', $this->paypal->account_email);
        $settings = $this->paypal;
        $this->assertEquals('abc@example.com', $settings->account_email);
        $this->paypal->save();
        $this->assertTrue($this->paypal->id > 0);
        $fetched = $this->paypal->dao->fetch($this->paypal->id);
        $this->assertEquals('abc@example.com', $fetched->account_email);
    }
    
    /**
     * Runs the use case for an instant payment notification (IPN).
     *
     * @param array $options For simulating various exception conditions
     * @return null
     */
    function doIpn($options=null)
    {
        if (!isset($options)) $options = array();
        
        if (gv($options, 'create_cart', true)) {
            $this->generator = new order_SampleGenerator;
            $this->cart = $this->generator->makeCart();
            mm_setCart($this->cart);
        }
        else {
            $this->cart = mvc_Model::fetch('cart_Cart', $this->cart->id);
        }
        $line = $this->cart->lines[0];

        ob_start();
        $this->paypal->renderSubmitOrderButton(new stdClass);
        ob_end_clean();

        if (gv($options, 'existing_order')) {
            $payment_method = new payment_DummyPaymentMethod;
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
            $this->cart->payment_method = $payment_method;
            $this->cart->setPropertyValues($cart_values);
            $this->assertTrue($this->cart->processOrder(), "Failed to process order: " . implode(', ', $this->cart->errors));
            $this->cart->save();
            $this->assertTrue(!empty($this->cart->order_id), "Cart should have an order id");
        }
        else {
            if (gv($options, 'mark_as_processed')) {
                $trans_dao = new paypal_TransactionDAO;
                $trans = $trans_dao->fetchByCartId($this->cart->id);
                $trans->status = 'Completed';
                $trans->save();
            }
        
            if (gv($options, 'delete_transaction_record')) {
                $trans_dao = new paypal_TransactionDAO;
                $trans = $trans_dao->fetchByCartId($this->cart->id);
                $trans->delete();
            }
        }
        
        $request = $this->paypal->cartToIpnRequest($this->cart);
        //echo "ipn request: " . var_export($request, true) . "\n";
        $request['receiver_email'] = gv($options, 'receiver_email', $this->paypal->account_email);
        $request['mc_gross'] = gv($options, 'mc_gross', $this->cart->total);
        $request['txn_id'] = (string) $this->paypal->trans->id;
        $request['payment_status'] = 'Completed';
        if ($remove_param = gv($options, 'remove_param')) {
            unset($request[$remove_param]);
        }

        if (gv($options, 'deactivate_product')) {
            $product_dao = new product_ProductDAO;
            foreach ($this->cart->lines as $i=>$line) {
                $product = $product_dao->fetchBySku($line->sku);
                $product->active = false;
                $this->assertTrue($product->save(), "Failed to save product: " . implode(', ', $product->errors));
                $product = $product_dao->fetchBySku($line->sku);
                break;
            }
        }
        
        if (gv($options, 'delete_cart')) {
            $this->cart->delete();
        }
        
        if (gv($options, 'delete_account_email')) {
            $this->paypal->account_email = null;
        }

        $poster = new mm_MockHttpPoster;
        $poster->body = gv($options, 'ipn_response', 'VERIFIED');
        $poster->throw_exception = gv($options, 'throw_poster_exception', false);
        $this->paypal->setPoster($poster);
        //$this->assertNotNull($this->paypal->_cart);
        $this->paypal->handleIpn($request);
        $this->assertEquals(gv($options, 'ipn_response', 'VERIFIED'), $this->paypal->ipn_response);
    }
    
    function testSubmitOrder() {
        $this->doIpn();
        $this->assertOrderCreated();
    }
    
    function assertOrderCreated($options=array())
    {
        $this->assertNotNull($this->paypal, "Should have paypal object");
        
        if (gv($options, 'has_errors', false)) {
            $this->assertTrue(empty($this->paypal->errors), "The paypal object should have errors.");
        }
        else {
            $this->assertEquals(0, count($this->paypal->errors), "Errors: " . implode(', ', $this->paypal->errors), "Errors: " . implode(", ", $this->paypal->errors));
        }

        $cart = mvc_Model::fetch('cart_Cart', $this->cart->id);
        $this->assertTrue($cart->order_id > 0, "Should have created an order");
        $order = $cart->order;
        if (gv($options, 'assert_payed', true)) {
            $this->assertTrue($order->payed, "Order should be marked as payed");
        }
        else {
            $this->assertTrue(empty($order->payed), "Order should NOT be marked as payed");
        }
        
        $trans_dao = new paypal_TransactionDAO;
        $trans = $trans_dao->fetchByCartId($this->cart->id);
        $this->assertTrue($trans->order_id > 0, "Transaction should have an order ID");
    }
    
    /**
     * Validation should fail when the 'custom' parameter is missing.
     */
    function testMissingCustomParam()
    {
        $this->doIpn(array('remove_param' => 'custom'));
        $this->assertTrue($this->paypal->ipn_ignored, "IPN should have been ignored");
    }
    
    function testMissingTransactionIdParam()
    {
        $this->doIpn(array('remove_param' => 'txn_id'));
        $this->assertHasErrorMatching('/txn_id/');
    }
    
    function testAlreadyProcessedTransaction()
    {
        $this->doIpn(array('mark_as_processed' => true));
        $this->assertHasErrorMatching('/already.*processed/i');
    }
    
    function testTransactionNoCart()
    {
        $this->doIpn(array('delete_cart' => true));
        $this->assertHasErrorMatching('/cart not found/i');
    }
    
    function testInvalidCartLines()
    {
        $this->doIpn(array('deactivate_product' => true));
        $this->assertHasErrorMatching('/no longer available/i', array('assert_order' => true));
    }
    
    function testMissingAccountEmail()
    {
        $this->doIpn(array('delete_account_email' => true));
        $this->assertHasErrorMatching('/missing account_email/i');
    }
    
    function testMisMatchedReceiverEmail()
    {
        $this->doIpn(array('receiver_email' => 'wrong@example.com'));
        $this->assertHasErrorMatching('/receiver_email.*doesn\'t match/i');
    }
    
    function testMisMatchedGross()
    {
        $this->doIpn(array('mc_gross' => '-19.99'));
        $this->assertHasErrorMatching('/doesn\'t match recorded total/i');
    }
    
    function testInvalidResponse()
    {
        $this->doIpn(array('ipn_response' => 'INVALID'));
        $this->assertHasErrorMatching('/server response/i');
    }
    
    function testThrownPosterException()
    {
        $this->doIpn(array('throw_poster_exception' => true, 'ipn_response' => NULL));
        $this->assertHasErrorMatching('/An exception occurred/i');
    }
    
    function testOtherPaymentMethod()
    {
        $this->doIpn(array('existing_order' => true));
        $this->assertTrue($this->paypal->ipn_ignored, "IPN should have been ignored");
    }
    
    function assertHasErrorMatching($pattern, $options=array())
    {
        if (gv($options, 'assert_order', false)) {
            $options['assert_payed'] = false;
            $options['has_errors'] = true;
            $this->assertOrderCreated($options);
        }
        else {
            $this->assertTrue(empty($this->cart->order_id), "Should not have created an order");
        }
        $this->assertTrue(count($this->paypal->errors) > 0, "Should have generated an error");
        $matches_error = false;
        foreach ($this->paypal->errors as $msg) {
            if (preg_match($pattern, $msg)) {
                $matches_error = true;
                break;
            }
        }
        $this->assertTrue($matches_error, "Should have generated an error matching pattern $pattern. Got instead: " . implode(', ', $this->paypal->errors));
        if (gv($options, 'assert_order', false)) {
            $this->assertTrue($this->paypal->sent_ipn_order_notice_email, "Should have sent an IPN order notice email");
        }
        else {
            $this->assertTrue($this->paypal->sent_ipn_error_email, "Should have sent an error email");
        }
    }

}
