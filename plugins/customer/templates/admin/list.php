<?php
/**
 * @package customer
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
?>

<?php if (!$this->customers): ?>
<div class="no_records_found">No customers were found</div>
<?php else: ?>
<?php $this->paginate(); ?>
<table class="records">
    <thead>
        <tr>
            <td>Name</td>
            <td>Email</td>
            <td>Creation Date</td>
            <td>Actions</td>
        </tr>
    </thead>
    <?php foreach ($this->customers as $customer): ?>
    <tbody>
        <tr>
            <td><?php ph($customer->billing_address->name) ?></td>
            <td><?php ph($customer->billing_address->email) ?></td>
            <td><?php ph(mm_date($customer->created_on)) ?></td>
            <td>
                <?php echo $this->linkTag('edit', '?a=customer_admin.edit&id=' . $customer->id) ?>&nbsp;&nbsp;
                <?php echo $this->linkTag('delete', '?a=customer_admin.delete&id=' . $customer->id, array('confirm' => "Are you sure you want to delete this account?")) ?>
            </td>
        </tr>
    </tbody>
    <?php endforeach ?>
</table>
<?php $this->paginate(); ?>
<?php endif ?>
