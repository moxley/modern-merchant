<?php
/**
 * @package paypalwpp
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class paypalwpp_Billing extends cart_Billing
{
    /**
     * Override validation.
     */
    function validate() {
        return array();
    }
}
