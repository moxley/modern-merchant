<?php
/**
 * @package paypal
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
?>
<?php if (!$this->transactions): ?>
<div class="no_records_found">No transactions were found.</div>
<?php else: ?>
<table class="records" cellspacing="0">
    <thead>
        <tr>
            <td>Date</td>
            <td>Transaction ID</td>
            <td>Status</td>
            <td>Cart ID</td>
            <td>Order ID</td>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($this->transactions as $trans): ?>
        <tr>
            <td><?php ph(mm_date($trans->creation_date)) ?></td>
            <td><?php ph($trans->txn_id) ?></td>
            <td><?php ph($trans->status) ?></td>
            <td><?php if ($trans->cart_id) echo $this->linkTag($trans->cart_id, "?a=cart_admin.show&id=$trans->cart_id") ?></td>
            <td><?php if ($trans->order_id) echo $this->linkTag($trans->order_id, "?a=order.edit&id=$trans->order_id") ?></td>
        </tr>
        <?php endforeach ?>
    </tbody>
</table>
<?php endif ?>