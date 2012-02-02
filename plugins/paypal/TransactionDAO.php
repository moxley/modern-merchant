<?php
/**
 * @package paypal
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class paypal_TransactionDAO
{
    public $status_types = array('Canceled_Reversal','Completed','Denied','Failed','Pending','Refunded','Reversed');
    public $select_columns = array(
        'id',
        'creation_date', 'postdata', 'txn_id', 'status',
        'order_id', 'sid', 'cart_id');
        
    function deleteAll()
    {
        $sql = "delete from mm_paypal_ipn_trans";
        mm_getDatabase()->query($sql);
    }
    
    function delete($trans)
    {
        mm_getDatabase()->execute("DELETE FROM mm_paypal_ipn_trans WHERE id=?", $trans->id);
        return true;
    }
    
    function getCount()
    {
        $sql = "select count(*) from mm_paypal_ipn_trans";
        return mm_getDatabase()->getOne($sql);
    }
    
    function add($trans)
    {
        $dbh = mm_getDatabase();
        $fmt = $dbh->getFormatter();
        $sql = sprintf("INSERT INTO mm_paypal_ipn_trans " .
                "(creation_date, postdata, txn_id, status, order_id, sid, cart_id)" .
                "values (%s, %s, %s, %s, %s, %s, %s)",
                $fmt->fDate($trans->creation_date),
                $fmt->fString($trans->postdata),
                $fmt->fString($trans->txn_id),
                $fmt->fString($trans->status),
                $fmt->fInt($trans->order_id),
                $fmt->fString($trans->sid),
                $fmt->fString($trans->cart_id));
        $dbh->execute($sql);
        $id = $dbh->lastInsertId();
        $trans->id = $id;
        return $trans;
    }
    
    function update($trans)
    {
        $dbh = mm_getDatabase();
        $fmt = $dbh->getFormatter();
        $sql = "UPDATE mm_paypal_ipn_trans SET" .
                " postdata=" . $fmt->fString($trans->postdata) .
                ",txn_id=" . $fmt->fString($trans->txn_id) .
                ",status=" . $fmt->fString($trans->status) .
                ",order_id=" . $fmt->fInt($trans->order_id) .
                ",sid=" . $fmt->fString($trans->sid) .
                ",cart_id=" . $fmt->fString($trans->cart_id) .
                " WHERE id=" . $fmt->fInt($trans->id);
        $dbh->query($sql);
        return $trans;
    }
    
    function fetch($id)
    {
        return $this->fetchBy('id', $id);
    }
    
    function fetchByCartId($cart_id)
    {
        return $this->fetchBy('cart_id', $cart_id);
    }
    
    function fetchByTxnId($txn_id)
    {
        return $this->fetchBy('txn_id', $txn_id);
    }
    
    function fetchBySid($sid)
    {
        return $this->fetchBy('sid', $sid, array('order' => 'creation_date desc'));
    }
    
    function fetchBy($col, $value, $options=null)
    {
        if (!$options) $options = array();
        $dbh = mm_getDatabase();
        $fmt = $dbh->getFormatter();
        $sql = "select *" .
                " from mm_paypal_ipn_trans" .
                " where $col=?";
        if (gv($options, 'order')) {
            $sql .= " ORDER BY " . $options;
        }
        $row = $dbh->getOneAssoc($sql, array($value));
        if (!$row) return null;
        $trans = $this->parseRow($row);
        return $trans;
    }
    
    function find($options=null)
    {
        if (!$options) $options = array();
        
        $order_clause = "";
        if (gv($options, 'order')) {
            $order_clause = "ORDER BY " . gv($options, 'order');
        }
        
        $limit_clause = "";
        if (gv($options, 'limit')) {
            $offset       = (int) gv($options, 'offset', 0);
            $limit        = (int) gv($options, 'limit', 50);
            $limit_clause = "LIMIT $offset, $limit";
        }
        
        $sql = "SELECT * FROM mm_paypal_ipn_trans $order_clause $limit_clause";
        $db = mm_getDatabase();
        $all = $db->getAllAssoc($sql);
        $transactions = array();
        foreach ($all as $trans) {
            $transactions[] = $this->parseRow($trans);
        }
        return $transactions;
    }
    
    function parseRow($row)
    {
        $dbh = mm_getDatabase();
        $fmt = $dbh->getFormatter();
        $trans = new paypal_TransactionDO;
        $trans->id = $fmt->pInt($row['id']);
        $trans->creation_date = $fmt->pDate($row['creation_date']);
        $trans->postdata = $row['postdata'];
        $trans->txn_id = $row['txn_id'];
        $trans->status = $row['status'];
        $trans->order_id = $fmt->pInt($row['order_id']);
        $trans->sid = $row['sid'];
        $trans->cart_id = $row['cart_id'];
        return $trans;
    }
    
    function getColumnsList($alias)
    {
        $pre = $alias ? $alias . '.' : '';
        $post = $alias ? ' ' . $alias : '';
        return $pre . implode(', ' . $pre , $this->select_columns);
    }
}
