<?php
/**
 * @package order
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
$this->target_action = "order.update";
$this->action_label = "Update";
$this->title = "Edit order #{$this->order->id}";
$this->render('order/_form');
