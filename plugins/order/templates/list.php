<?php
/**
 * @package order
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
?>
<?php
if (!$this->orders):
?>
<div class="no_records_found">No records found</div>
<?php else: ?>
<?php $this->paginate(); ?>
<table cellspacing="0" class="records">
    <thead>
        <tr> 
            <td>ID</td>
            <td>Cart</td>
            <td>Date</td>
            <td>Sub Total</td>
            <td>Total</td>
            <td>Action</td>
        </tr>
    </thead>
    <tbody>
        <?php
        $rowClasses = array("dataRowEven", "dataRowOdd");
        foreach ($this->orders as $r=>$order):
            $class = $rowClasses[ ($r+1)%2 ];
        ?>
        <tr class="<?php print $class ?>"> 
            <td><?php ph($order->id) ?></td>
            <td><?php if ($order->cart_id) echo $this->linkTag($order->cart_id, '?a=cart_admin.show&id='.$order->cart_id) ?></td>
            <td><?php ph(mm_datetime($order->order_date)) ?></td>
            <td><?php ph($order->sub_total) ?></td>
            <td><?php ph($order->total) ?></td>
            <td>
                <a href="<?php ph($this->adminBaseUrl()) ?>?action=order.edit&amp;id=<?php ph($order->id) ?>">Edit</a>
                <a href="<?php ph($this->urlFor(array('a' => 'order.delete', 'id' => $order->id))); ?>" onclick="return confirm('Are you sure?')">Delete</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif ?>
<?php $this->paginate(); ?>
