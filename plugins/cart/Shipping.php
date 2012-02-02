<?php
/**
 * @package cart
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

class cart_Shipping extends addr_Address
{
    public $billing_same = true;
    function validate() {
        $errors = array();
        $this->trimStrings();
        if (!$this->email) {
            $errors[] = "Please provide a Shipping email address";
        }
        $errors = array_merge($errors, parent::validate());
        return $errors;
    }
}
