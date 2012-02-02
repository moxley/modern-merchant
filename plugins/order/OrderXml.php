<?php
/**
 * @package order
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class order_OrderXml
{
    public $addr_nodes;
    private $order_basic;
    
    function __construct()
    {
        $this->addr_nodes = array(
            'first_name', 'last_name', 'email', 'address_1', 'address_2',
            'city', 'state', 'zip', 'country', 'phone_day', 'phone_night',
            'company');
        $this->order_basic = array('date', 'creation_date',
            'modify_username', 'ship_total',
            'ship_date', 'tracking',
            'cust_approved', 'payed', 'unique_code',
            'session_id', 'cust_comments', 'notes',
            'shipping_method_id', 'payment_method_id',
            'previous_customer', 'date');
    }
    
    function formatOrder($order)
    {
        $xml = '<order id="' . x($order->id) . '">';
        foreach ($this->order_basic as $attrib) {
            if (is_bool($order->$attrib)) {
                $xml .= "<$attrib>" . ($order->$attrib?'true':'false') . "</$attrib>";
            }
            else if (endswith($attrib, 'total')) {
                $xml .= "<$attrib>" . number_format($order->$attrib, 2) . "</$attrib>";
            }
            else {
                $xml .= "<$attrib>" . x($order->$attrib) . "</$attrib>";
            }
        }
        $xml .=    $this->formatLines($order->lines);
        $xml .= $this->formatAddress('billing_addr', $order->billing_addr);
        $xml .= $this->formatAddress('shipping_addr', $order->shipping_addr);
        $xml .= '</order>';
        return $xml;
    }
    
    function parseOrder($source, $order=null)
    {
        $doc = new DOMDocument;
        $doc->loadXML($source);
        $elements = $doc->getElementsByTagName('order');
        if ($elements->length == 0) throw new Exception("Cannot find parent <order> node");
        return $this->parseOrderNode($elements->item(0), $order);
    }
    
    function parseOrderNode($node, $order=null)
    {
        if (!$order) $order =  new order_Order;
        $order->creation_date = null;
        $order->date = null;
        
        $children = $node->childNodes;
        for ($i = 0; $i < $children->length; $i++) {
            $child = $children->item($i);
            if ($child->nodeType != XML_ELEMENT_NODE) continue;
            switch ($child->nodeName) {
            case 'lines':
                $order->lines = $this->parseLinesNode($child);
                break;
            case 'billing_addr':
                $order->billing_addr = $this->parseAddressNode($child);
                break;
            case 'shipping_addr':
                $order->shipping_addr = $this->parseAddressNode($child);
                break;
            }
        }
        $xmlutil = new mm_Xml;
        $assoc = $xmlutil->toAssoc($node);
        $order->id = $node->getAttribute('id');
        if ($order->id) $order->id = (int) $order->id; 
        $order->date = gvTime($assoc, 'date');
        $order->creation_date = gvTime($assoc, 'creation_date');
        $order->modify_username = $assoc['modify_username'];
        $order->ship_total = gvMoney($assoc, 'ship_total');
        $order->ship_date = gvFloat($assoc, 'ship_date');
        $order->shipping_method_id = gvInt($assoc, 'shipping_method_id');
        $order->payment_method_id = gvInt($assoc, 'payment_method_id');
        $order->tracking = $assoc['tracking'];
        $order->cust_approved = $this->parseBool($assoc['cust_approved']);
        $order->payed = $this->parseBool($assoc['payed']);
        $order->previous_customer = $this->parseBool($assoc['previous_customer']);
        $order->unique_code = $assoc['unique_code'];
        $order->session_id = $assoc['session_id'];
        $order->cust_comments = $assoc['cust_comments'];
        $order->notes = $assoc['notes'];
        return $order;
    }
    
    function parseBool($value)
    {
        if ($value == 'true') return true;
        return false;
    }
    
    function parseLinesNode($node)
    {
        $lines = array();
        $nodes = $node->childNodes;
        foreach ($nodes as $node) {
            if ($node->nodeType != XML_ELEMENT_NODE) continue;
            if ($node->nodeName != 'line') {
                throw new Exception("Unexpected element <{$node->nodeName}>");
            }
            $lines[] = $this->parseLineNode($node);
        }
        return $lines;
    }
    
    function parseLineNode($node)
    {
        $util = new mm_Xml;
        $line = new cart_CartLine;
        $line->id = $node->getAttribute('id');
        $assoc = $util->toAssoc($node);
        //$line->product_id = $assoc['product_id'];
        $line->sku = $assoc['sku'];
        $line->qty = (int) $assoc['qty'];
        $line->price = $assoc['price'];
        $line->description = $assoc['description'];
        return $line;
    }
    
    function parseAddressNode($node)
    {
        $addr = new addr_Address;
        $addr->id = (int) $node->getAttribute('id');
        $util = new mm_Xml;
        $assoc = $util->toAssoc($node);
        $attribs = array(
            'first_name', 'last_name', 'email', 'address1', 'address_1', 'address2', 'address_2',
            'city', 'state', 'zip', 'country', 'phone_day', 'phone_night',
            'company'
        );
        foreach ($attribs as $attrib) {
            if (array_key_exists($attrib, $assoc)) {
                $addr->$attrib = $assoc[$attrib];
            }
        }
        return $addr;
    }
    
    function formatLine($line)
    {
        return '<line id="' . x($line->id) . '">' .
                '<sku>' . x($line->sku) . '</sku>' .
                '<description>' . x($line->description) . '</description>' .
                '<price>' . x(number_format($line->price, 2)) . '</price>' .
                '<qty>' . x($line->qty) . '</qty>' .
                '</line>';
    }
    
    function formatLines($lines)
    {
        $xml = '<lines>';
        foreach ($lines as $line) {
            $xml .= $this->formatLine($line);
        }
        $xml .= '</lines>';
        return $xml;
    }

    function formatAddress($tag, $address)
    {
        if ($address == null) {
            $xml = '<' . $tag . '/>';
        }
        else {

            $xml = '<' . $tag . ' id="' . x($address->id) . '">';
            foreach ($this->addr_nodes as $name) {
                if (!is_array($name)) {
                    $xml .= "<$name>" . x($address->$name) . "</$name>";
                }
            }
            $xml .= '</' . $tag . '>';
        }
        return $xml;
    }    
    
}
