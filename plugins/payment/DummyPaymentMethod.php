<?php
/**
 * @package payment
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * Dummy payment method for testing.
 */
class payment_DummyPaymentMethod extends mvc_Model
{
    public $processed = false;
    
    function process()
    {
        $this->processed = true;
        return true;
    }
}
