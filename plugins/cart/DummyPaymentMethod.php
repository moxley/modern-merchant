<?php
/**
 * @package cart
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class cart_DummyPaymentMethod extends payment_PaymentMethod
{
    function __construct($values=null) {
        $this->name = uniqid('payment_');
        parent::__construct($values);
    }
    
    function process($cart)
    {
        return true;
    }
}
