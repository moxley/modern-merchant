<?php
/**
 * @package cart
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
?>
=====================================
Billing Detail
-------------------------------------
<?php
$this->address = $this->billing;
mm_renderContent('order.email.address', $this);
?>
-------------------------------------

