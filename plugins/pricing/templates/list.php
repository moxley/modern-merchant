<?php
/**
 * @package pricing
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
?>
<style type="text/css">
    .dataTable tbody td { text-align: center; }
    .number { text-align: right; }
</style>

<?php if ($this->pricings) : ?>
<table cellspacing="0" class="records">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Type</th>
            <th>Value</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
<?php foreach ($this->pricings as $pricing) : ?>
        <tr>
            <td class="first"><?php ph($pricing->id) ?></td>
            <td><?php ph($pricing->name) ?></td>
            <td><?php ph($pricing->type) ?></td>
            <td style="number"><?php ph($pricing->value) ?></td>
            <td>
                <a href="?action=pricing.edit&amp;id=<?php ph($pricing->id) ?>">Edit</a>
                &nbsp;
                <a href="?action=pricing.delete&amp;id=<?php ph($pricing->id) ?>" onclick="return confirm('Are you sure?')">Delete</a>
            </td>
        </tr>
<?php endforeach; ?>
    </tbody>
</table>
<?php else: ?>
    There are no pricings. <a href="?a=pricing.new">Create one?</a>
<?php endif; ?>
