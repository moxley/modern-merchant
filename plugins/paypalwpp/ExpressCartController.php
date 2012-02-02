<?php
/**
 * @package paypalwpp
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class paypalwpp_ExpressCartController extends cart_Controller
{
    private $_express_method = null;
    
    function runExpressPressedAction()
    {
        $express_method = $this->getExpressMethod();
        $token = $express_method->getNewToken($this->cart);
        if (!$token) {
            $this->addWarning("There was an error: " . implode(', ', $express_method->errors));
            $this->redirectToAction('cart.shippingPage');
            return false;
        }
        else {
            $this->cart->payment_method = $express_method;
            $url = $express_method->getExpressCheckoutUrl($token);
            $this->cart->save();
            $this->redirect(array('url'=>$url));
            return false;
        }
    }
    
    public function runReturnFromExpressAction()
    {
        $token = $this->request['token'];
        $this->getExpressMethod()->loadBillingInfo($token);
        $this->redirectToAction('cart.verifyPage');
        return false;
    }

    private function getExpressMethod()
    {
        if (!$this->_express_method) {
            $dao = new payment_PaymentMethodDAO;
            $this->_express_method = $dao->fetchByName('paypalwpp_express');
        }
        return $this->_express_method;
    }
    
    public function runCancelExpressAction()
    {
        $this->addNotice("PayPal payment cancelled");
        $this->redirectToAction('cart.paymentPage');
        return false;
    }

    function runShippingPageAction()
    {
        $methods = new payment_PaymentMethodDAO;
        $express = $methods->getModuleByName('paypalwpp_express');
        $ret = parent::runShippingPageAction();
        if ($express && $express->isActive()) {
            if ($this->cart->validForCheckout()) {
                $this->render('paypalwpp/express');
            }
        }
        return $ret;
    }

    function runSubmitPaymentAction()
    {
        // Let the cart know what the payment method is
        $cart = $this->getCart();
        $cart->setPropertyValues($this->req('cart'));
        
        $errors = $cart->validateForCheckout();
        if ($errors) {
            $this->cart->adjustLines();
            $this->addWarnings($errors);
            $cart->save();
            $this->redirectToAction('cart');
            return false;
        }
        
        $this->cart->save();
        $errors = $cart->validatePaymentMethod();
        if ($errors) {
            $this->addWarnings($errors);
            $this->redirectToAction('cart.paymentPage');
            return false;
        }
        
        $this->session->set('after_order_cart_id', $cart->id);

        $express_method = $this->getExpressMethod();
        if ($this->cart->payment_method_id == $express_method->id) {
            $token = $express_method->getNewToken($this->cart);
            if (!$token) {
                $this->addWarning("There was an error: " . implode(', ', $express_method->errors));
                $this->redirectToAction('cart.shippingPage');
                return false;
            }
            else {
                $this->cart->payment_method = $express_method;
                $url = $express_method->getExpressCheckoutUrl($token);
                $this->cart->save();
                $this->redirect(array('url'=>$url));
                return false;
            }
        }
        else {
            $this->redirectToAction('cart.verifyPage');
            return false;
        }
    }
    
    function getModuleName() { return 'cart'; }
}
