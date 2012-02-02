<?php
/**
 * @package payment
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class payment_CreditCardPayment extends mvc_Model {
    public $cc_name;
    public $cc_type;
    public $cc_number;
    public $cc_exp_month;
    public $cc_exp_year;
    public $cc_cvv;
    
    public function validate() {
        $errors = array();
        $this->trimStrings();
        if (!$this->cc_name) {
            $errors[] = "Please enter the cardholder's name";
        }
        if (!$this->cc_type) {
            $errors[] = "Please select a card type";
        }
        if (!$this->cc_number) {
            $errors[] = "Please enter the card number";
        }
        if (!$this->cc_exp_month) {
            $errors[] = "Please enter the expiration month";
        }
        if (!$this->cc_exp_year) {
            $errors[] = "Please enter the expiration year";
        }
        if (!$this->cc_cvv) {
            $errors[] = "Please enter the CVV number for the card";
        }
        return $errors;
    }
    
    function getExpirationDate() {
        return mktime(0,0,0, $this->cc_exp_month, 1, $this->cc_exp_year);
    }
}
