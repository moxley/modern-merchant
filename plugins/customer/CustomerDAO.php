<?php
/**
 * @package customer
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * @package customer
 */
class customer_CustomerDAO extends mvc_DataAccess
{
    public $regular_columns = array(array('billing_address_id', 'type'=>'integer'), array('shipping_address_id', 'type'=>'integer'), array('created_on', 'type'=>'datetime'), array('user_id', 'type' => 'integer'));
    
    public function findBySearch($q, $offset, $maxPerPage)
    {
        $sql = array('SELECT c.* FROM mm_customer c'
            . ' INNER JOIN mm_user u ON u.id=c.user_id'
            . ' LEFT JOIN mm_address a ON a.id=c.billing_address_id'
            . ' WHERE lower(u.username) = lower(?) OR lower(a.first_name) = lower(?) OR lower(a.last_name) = lower(?) OR lower(a.email) = lower(?)', $q, $q, $q, $q);
        $count = $this->count(array('sql' => $sql));
        $list = $this->find(array('sql' => $sql));
        mm_log("list: ", $list);
        return array($list, $count);
    }
}
