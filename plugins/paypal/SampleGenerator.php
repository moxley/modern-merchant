<?php
/**
 * @package paypal
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class paypal_SampleGenerator
{
    private $dg;
    
    function __construct()
    {
        $this->dg = new test_DataGenerator;
    }
    
    function makeTransaction()
    {
        $trans = new paypal_TransactionDO;
        $trans->id = $this->dg->makeIntId();
        $trans->creation_date = $this->dg->makeDate();
        $trans->postdata = $this->dg->makeString(300);
        $trans->txn_id = $this->dg->makeUnique(20);
        $types = paypal_TransactionDO::getStatusTypes();
        $index = rand(0, count($types)-1);
        $trans->status = $types[$index];
        $trans->order_id = $this->dg->makeIntId();
        $trans->session_id = $this->dg->makeIntId();
        $trans->sid = $this->dg->makeUnique(30);
        $trans->cart_id = $this->dg->makeUnique(20);
        return $trans;
    }
}
