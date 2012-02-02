<?php
/**
 * @package paypal
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class paypal_TransactionDO
{
    public $id;
    public $creation_date;
    public $postdata;
    public $txn_id;
    public $status;
    public $order_id;
    public $session_id;
    public $sid;
    public $cart_id;
    public $cart;
    
    function __construct()
    {
        $this->creation_date = mm_time();
    }
    
    public static function getStatusTypes()
    {
        return array('Canceled_Reversal','Completed','Denied','Failed','Pending','Refunded','Reversed');
    }
    
    function populateFromCartAndSession($cart, $sess)
    {
        $this->populateFromCart($cart);
        $this->sid = $sess->sid;
        $this->session_id = $sess->id;
    }
    
    function populateFromCart($cart)
    {
        $this->cart = $cart;
        $this->cart_id = $cart->id;
        $this->order_id = $cart->order_id;
        $this->sid = $cart->getSID();
    }
    
    function save()
    {
        $dao = new paypal_TransactionDAO;
        if ($this->id) {
            $dao->update($this);
        }
        else {
            $dao->add($this);
        }
        return true;
    }
    
    function delete()
    {
        $dao = new paypal_TransactionDAO;
        $dao->delete($this);
        return true;
    }
}
