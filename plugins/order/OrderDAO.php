<?php
/**
 * @package order
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class order_OrderDAO extends mvc_DataAccess
{
    public $regular_columns = array('order_date', 'creation_date', 'ship_date', 'modify_user', 'customer_id', 'sub_total', 'shipping_method_id', 'payment_method_id', 'tracking', 'cust_approved', 'payed', 'unique_code', 'cart_id', 'session_id', 'data', 'notes');
    protected $select_columns = "o.id, o.order_date, o.creation_date, o.ship_date, o.modify_user, o.customer_id, o.sub_total, o.ship_total, o.shipping_method_id, o.payment_method_id, o.tracking, o.total, o.cust_approved, o.payed, o.unique_code, o.cart_id, o.session_id, o.data, o.notes";
    
    function fetchByCartId($cart_id)
    {
        return $this->fetchByUniqueCode($cart_id);
    }
    
    function fetchByUniqueCode($code)
    {
        $dbh = mm_getDatabase();
        $fmt = $dbh->getFormatter();
        $sql = "select {$this->select_columns} from mm_order o " .
            "where o.unique_code=" . $fmt->fString($code);
        $row = $dbh->getOneAssoc($sql);
        if (!$row) return null;
        $order = $this->parseRow($row);
        return $order;
    }
        
    function parseRow($row, $options=array())
    {
        $fmt = mm_getDatabase()->getFormatter();
        $order = new order_Order;
        $order->order_date = $fmt->pDate($row['order_date']);
        $order->creation_date = $fmt->pDate($row['creation_date']);
        $order->ship_date = $fmt->pDate($row['ship_date']);
        $order->payment_method_id = $fmt->pInt($row['payment_method_id']);
        $order->unique_code = $row['unique_code'];
        $order->cart_id = $row['cart_id'] ? intval($row['cart_id']) : null;
        $order->customer_id = $row['customer_id'] ? intval($row['customer_id']) : null;
        $order->session_id = $row['session_id'];
        $order->modify_username = $row['modify_user'];
        $order->tracking = $row['tracking'];
        $order->cust_approved = $fmt->pBoolTF($row['cust_approved']);
        $order->payed = $fmt->pBoolTF($row['payed']);
        $order->notes = $row['notes'];
        $this->parseData($order, $row['data']);
        $order->id = (int) $row['id'];
        return $order;
    }
        
    function parseData($order, $data)
    {
        if (!$data) return;
        if (startswith($data, '<order')) {
            $orderxml = new order_OrderXml;
            $orderxml->parseOrder($data, $order);
        }
        else {
            $cart = $this->parseCartFromOrderData($order, $data);
            $order->populateFromCart($cart);
        }
    }
        
    /**
     * Parse mm_order.data contents that is in old-style format (pre 0.03)
     *  
     * @deprecated
     * @since 0.03
     */
    function parseCartFromOrderData($order, $s_data)
    {
        $data = unserialize($s_data);
        if (!$data) return null;
        if (is_array($data)) {
            $us_cart = $data['cart'];
        }
        else {
            $us_cart = $data;
        }
            
        $us_class = get_class($us_cart);
        if ($us_class != 'Cart') {
            $cart = $us_cart;
            foreach ($cart->lines as $line) {
                $attribs = array('sku', 'price', 'qty', 'description', 'id');
                foreach ($attribs as $attrib) {
                    if (!isset($line->$attrib)) {
                        $line->$attrib = $line->data[$attrib];
                    }
                }
            }
        }
        else {
            /*
             * Convert from old class model
             */
            $cart = new cart_Cart;
            foreach ($us_cart->lines as $us_line) {
                $line = new cart_CartLine;
                $line->id = $us_line->data['id'];
                $line->qty = $us_line->data['qty'];
                $line->price = $us_line->data['price'];
                $line->sku = $us_line->data['sku'];
                $line->description = $us_line->data['description'];
                $line->data = $us_line->data;
                $cart->lines[] = $line;
            }
            $cart->complete = $us_cart->cust_approved;
            $cart->creation_date = (int) $order->creation_date;
            $cart->order_date = $order->date;
            $cart->cust_approved = $us_cart->cust_approved;
            $cart->error = null;
            $cart->order_id = $us_cart->order_id;
            $cart->order_values = $us_cart->orderValues;
            $cart->payed = $us_cart->payed;
            $cart->payment_method_id = $us_cart->payment_method_id;
            $cart->session_id = $us_cart->sess_id;
            $cart->ship_calc = $us_cart->shipCalc;
            $cart->ship_types = array();
            foreach ($us_cart->shipTypes as $type) {
                $t = $type;
                $t['shipping_method_id'] = $type['id'];
                $cart->ship_types[] = $t;
            }
            $cart->shipping_functions = null;
            $cart->unique_code = $us_cart->unique_code;
            $cart->user_message = null;
        }
            
        return $cart;
    }
    
    function add($order)
    {
        $dbh = mm_getDatabase();
        
        if (false) {
            // Old serialization
            $cart = new cart_Cart;
            $cart->populateFromOrder($order);
            $data = serialize($cart);
        }
        else {
            $orderxml = new order_OrderXml;
            $data = $orderxml->formatOrder($order);
        }
        
        if (!$order->session_id) $order->session_id = session_id();
        
        $query = sprintf("INSERT INTO mm_order (" .
                         "creation_date, " .
                         "order_date, " .
                         "ship_date, " .
                    
                         "modify_user, " .
                         "shipping_method_id, " .
                         "tracking, " .
                         "sub_total, " .
                    
                         "ship_total, " .
                         "total, " .
                         "payed, " .
                         "payment_method_id, " .
                    
                         "cust_approved, " .
                         "data, " .
                         "notes, " .
                         "unique_code, " .
                    
                         "session_id," .
                         "customer_id," .
                         "cart_id" .
                         ") values (" .
                         "%s, %s, %s, " .
                         "%s, %s, %s, %s, " .
                         "%s, %s, %s, %s, " .
                         "%s, %s, %s, %s, " .
                         "%s, %s, %s)",
                         $this->dbDate($order->creation_date),
                         $this->dbDate($order->order_date),
                         $this->dbDate($order->ship_date),
                    
                         dq($order->modify_username),
                         intval($order->shipping_method_id),
                         dq($order->tracking), 
                         $this->dbMoney($order->sub_total),
                    
                         $this->dbMoney($order->ship_total),
                         $this->dbMoney($order->total),
                         dq($order->payed ? 'T' : 'F'),
                         intval($order->payment_method_id),
                    
                         $this->dbBool($order->cust_approved),
                         dq($data),
                         dq($order->notes),
                         dq($order->unique_code),
                    
                         dq($order->session_id),
                         $this->dbInt($order->customer_id),
                         $this->dbInt($order->cart_id));
        $dbh->execute($query);
        $order->id = $dbh->lastInsertId();
        return $order;
    }
    
    function dbDate($time)
    {
        if (!$time) return 'null';
        return 'from_unixtime(' . intval($time) . ')';
    }
        
    function dbBool($bool)
    {
        return $bool ? "'T'" : "'F'";
    }
    
    function dbInt($int)
    {
        if ($int === null) return 'NULL';
        else return intval($int);
    }
        
    function dbMoney($money)
    {
        if (!isset($money)) return 'NULL';
        return number_format($money, 2);
    }
        
    function update($order)
    {
        $dbh = mm_getDatabase();
        $orderxml = new order_OrderXml;
        $data = $orderxml->formatOrder($order);
        
        $sql = "update mm_order set " .
            "sub_total=" . $this->dbMoney($order->sub_total) .
            ",ship_total=" . $this->dbMoney($order->ship_total) .
            ",total=" . number_format($order->total, 2) .
            ",ship_date=" . $this->dbDate($order->ship_date) .
            ",modify_user=" . dq($order->modify_username) .
            ",shipping_method_id=" . intval($order->shipping_method_id) .
            ",payment_method_id=" . intval($order->payment_method_id) .
            ",tracking=".dq($order->tracking) .
            ",payed=" . dq($order->payed ? 'T' : 'F') .
            ",cust_approved=" . $this->dbBool($order->cust_approved) .
            ",notes=" . dq($order->notes) .
            ",data=" . dq($data) .
            ",order_date=" . $this->dbDate($order->order_date) .
            ",session_id=" . dq($order->session_id) .
            ",unique_code=" . dq($order->unique_code) .
            ' WHERE id='.intval($order->id);
        $dbh->query($sql);
        return $order;
    }
    
    function count($where=null)
    {
        $db = mm_getDatabase();
        $where = $where ? "WHERE $where" : '';
        return $db->getOne("SELECT COUNT(*) FROM mm_order $where");
    }
    
    function find($options=array())
    {
        if ($where = array_delete_at($options, 'where')) {
            $options['where'] = $where;
        }
        return parent::find($options);
    }
}
