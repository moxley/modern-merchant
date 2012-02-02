<?php
/**
 * @package cart
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class cart_CartDAO extends mvc_DataAccess
{
    function parseRow($row, $options=array())
    {
        if (!$row) return null;
        $cart = unserialize($row['data']);
        $cart->id = (int) $row['id'];
        $cart->sid = $row['sid'];
        $cart->creation_date = strtotime($row['creation_date']);
        return $cart;
    }
}
