<?php
/**
 * @package order
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class order_Controller extends admin_Controller
{
    public $ov_titles;
    public $ship_method_options;
    public $pay_method_options;
    public $new_line = 'new_line';
    public $dao;
    
    function __construct() {
        $this->dao = new order_OrderDAO;
    }

    function requireOrder() {
        $id = $this->getRequiredParam('id');
        $this->order = $this->dao->fetch($id);
        if (!$this->order) {
            throw new Exception("No order not found for id=$id");
        }
        return $this->order;
    }

    function runListAction()
    {
        $this->max_results = $this->getMaxResults();
        $this->offset = $this->getOffset();
        $session = mm_getSession();

        if (!$this->req('offset')) {
            $saved_offset = $session->get("order.list.offset");
            if ($saved_offset) $offset = $saved_offset;
        }
        else {
            $session->set("order.list.offset", $offset);
        }
        
        $this->count = $this->dao->count();
        $params = array('offset' => $this->offset, 'limit' => $this->max_results, 'order' => 'id DESC');
        $this->orders = $this->dao->find($params);

        $this->results_nav = $this->getResultsNav(
            $this->count,
            $this->offset,
            $this->max_results,
            8,
            array('a' => 'order.list')
            );
        $this->title = "Orders";
    }
    
    function runEditAction()
    {
        $this->order = $this->requireOrder();
        $this->title = "Edit Order #{$this->order->id}";
    }
    
    function runUpdateAction()
    {
        $this->order = $this->requireOrder();
        $this->order->setAdminValues($this->req('order'));
        if (!$this->order->save()) {
            $this->addWarnings($this->order->errors);
            $this->setTemplate('order/edit');
        } else {
            $this->addNotice("Order successfully updated.");
            $this->redirectToAction('order.edit', array('id'=>$this->order->id));
            return false;
        }
    }
    
    function runNewAction()
    {
        $this->order = new order_Order;
        $this->order->modify_user = mm_getUser();
        $this->title = "Create New Order";
    }
    
    function runAddAction()
    {
        $this->order = new order_Order;
        $this->order->modify_user = mm_getUser();
        $this->order->setAdminValues($this->req('order'));
        if (!$this->order->save()) {
            $this->addWarnings($this->order->errors);
            $this->setTemplate('order/edit');
        } else {
            $this->addNotice("Order successfully created.");
            $this->redirectToAction('order.edit', array('id'=>$this->order->id));
            return false;
        }
    }
        
    function runDeleteAction()
    {
        $this->order = $this->requireOrder();
        $this->order->delete();
        $this->addNotice("Order successfully deleted.");
        $this->redirectToAction('order.list');
        return false;
    }
    
    function runCancelAction()
    {
        $this->redirectToAction('order');
        return false;
    }
    
    function runDefaultAction()
    {
        $this->setForward('order.list');
    }
    
    function runResendToCustomerAction()
    {
        $this->requireOrder();
        $cart = new cart_Cart;
        $cart->populateFromOrder($this->order);
        if (!$cart->sendCustomerEmail()) {
            $this->addWarning("Failed to send customer email");
        }
        else {
            $this->addNotice("Sent order to customer");
        }

        $this->redirectToAction('order.edit', array('id'=>$this->order->id));
        return false;
    }
    
    function runResendToSalesAction()
    {
        $order = $this->requireOrder();
        $cart = new cart_Cart;
        $cart->populateFromOrder($order);
        if (!$cart->sendSalesEmail()) {
            $this->addWarning("Failed to send sales email");
        }
        else {
            $this->addNotice("Sent order to sales");
        }

        $this->redirectToAction('order.edit', array('id'=>$order->id));
        return false;
    }
    
    /************************
     **
     ** Utility Methods
     **
     ************************/        
    
    function getShippingMethodOptions()
    {
        $this->dao = new shipping_ShippingMethodDAO;
        $methods = $this->dao->getAll();
        $options = array();
        foreach ($methods as $m) {
            $options[$m->id] = $m->name . ($m->active ? '' : ' (inactive)');
        }
        return $options;
    }
    
    function getPaymentMethodOptions()
    {
        $this->dao = new payment_PaymentMethodDAO;
        $methods = $this->dao->getList();
        $options = array();
        foreach ($methods as $m) {
            $options[$m->id] = $m->title . ($m->active ? '' : ' (inactive)');
        }
        return $options;
    }

    function preViewFilter()
    {
        parent::preViewFilter();
        $this->nav_template = "order/_nav";
    }
}
