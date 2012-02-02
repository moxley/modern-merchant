<?php
/**
 * @package cart
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
?>
<?php if (!$this->carts): ?>
<p class="no_records_found">No carts found</p>
<?php else: ?>

<?php $this->paginate() ?>

<table class="records" cellspacing="0">
    <thead>
        <tr>
            <td>ID</td>
            <td>Date</td>
            <td>Order ID</td>
            <td>Session</td>
            <td>Actions</td>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($this->carts as $cart): ?>
        <tr>
            <td><?php ph($cart->id) ?></td>
            <td><?php ph(mm_datetime($cart->creation_date)) ?></td>
            <td><?php if ($cart->order_id) echo $this->linkTag($cart->order_id, '?a=order.edit&id=' . $cart->order_id) ?></td>
            <td><?php if ($cart->sid) echo $this->linkTag('...' . substr($cart->sid, -8), '?a=sess_admin.show&sid=' . $cart->sid) ?></td>
            <td><?php echo $this->linkTag('view', '?a=cart_admin.show&id=' . $cart->id) ?></td>
        </tr>
        <?php endforeach ?>
    </tbody>
</table>

<?php $this->paginate() ?>

<?php endif ?>
