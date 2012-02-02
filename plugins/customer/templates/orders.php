<?php
/**
 * @package customer
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
?>

<?php $this->renderToArea('.secondLevelNav .area1', 'customer/_nav') ?>

<?php $this->title = "Your Order History" ?>

<div class="content">
    <?php if (!$this->orders): ?>
        <p>No orders were found for your account.</p>
    <?php else: ?>
    <table style="width: 800px" cellpadding="0" cellspacing="0" class="data">
        <thead>
            <tr>
                <th>Order Date</th>
                <th>ID</th>
                <th>Total</th>
                <th>Payed</th>
                <th>Ship Via</th>
                <th>Ship Date</th>
                <th>Tracking</th>
            </tr>
        </thead>
        <tbody>
    <?php foreach ($this->orders as $order): ?>
            <tr>
                <td><?php ph(mm_datetime($order->order_date)) ?></td>
                <td><?php ph($order->id) ?></td>
                <td><?php ph($order->total) ?></td>
                <td><?php ph($order->payed ? 'Yes' : 'No') ?>
                <td><?php ph($order->shipping_method->title) ?></td>
                <td><?php ph(mm_datetime($order->ship_date)) ?></td>
                <td><?php ph($order->tracking) ?></td>
            </tr>
    <?php endforeach ?>
        </tbody>
    </table>
    <?php endif ?>
</div>
