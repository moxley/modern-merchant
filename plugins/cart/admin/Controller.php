<?php
/**
 * @package cart
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * @package cart
 */
class cart_admin_Controller extends admin_Controller
{
    function runShowAction() {
        $this->requireCart();
        $this->title = "Cart Detail";
    }
    
    function runListAction() {
        $this->count = cart_Cart::dao()->count();
        $this->carts = cart_Cart::dao()->find(array('order' => 'creation_date DESC', 'limit' => $this->max_per_page, 'offset' => $this->offset));
        $this->results_nav = $this->getResultsNav($this->count, $this->offset, $this->max_per_page, $this->max_page_links);
        $this->title = "Shopping Carts";
    }

    function requireCart() {
        $id = $this->getRequiredParam('id');
        $this->cart = mvc_Model::fetch('cart_Cart', $id);
        if (!$this->cart) {
            throw new Exception("Failed to find cart for id=$id");
        }
        return $this->cart;
    }
}
