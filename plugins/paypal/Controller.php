<?php
/**
 * @package paypal
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class paypal_Controller extends admin_Controller
{
    function runTransactionsAction() {
        $tdao = new paypal_TransactionDAO;
        $this->transactions = $tdao->find(array('offset' => 0, 'limit' => 50, 'order' => 'creation_date DESC'));
        $this->title = "IPN Transactions";
    }
}
