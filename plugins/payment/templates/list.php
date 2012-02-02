<?php
/**
 * @package payment
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
?>
<?php if (!$this->payment_methods): ?>
<div class="no_records_found">No records found</div>
<?php else: ?>
<?php $this->paginate() ?>
<table cellspacing="0" class="records">
    <thead>
        <tr>
            <td>#</td>
            <td>Payment Method</td>
            <td>Active</td>
            <td>Default</td>
            <td>Action</td>
        </tr>
    </thead>
    <tbody>
        <?php
        $row_classes = array("dataRowEven", "dataRowOdd");
        foreach ($this->payment_methods as $r=>$method):
            $class = $row_classes[ ($r+1)%2 ];
        ?>
        <tr class="<?php ph($class) ?>"> 
            <td><?php ph($r+1) ?></td>
            <td><?php ph($method->title) ?></td>
            <td>
                <?php ph($method->active_title) ?>
                <?php if( $method->active ): ?>
                <span style="color: #0a0; font-weight: bold">active</span> <a href="<?php ph($this->adminBaseUrl()) ?>?a=payment.deactivate&amp;id=<?php ph($method->id) ?>">Deactivate</a>
                <?php else: ?>
                <span style="font-weight: bold">disabled</span> <a href="<?php ph($this->adminBaseUrl()) ?>?a=payment.activate&amp;id=<?php ph($method->id) ?>">Activate</a>
                <?php endif ?>
            </td>
            <td>
                <?php if ($method->is_default): ?>
                <span style="color: #0a0; font-weight: bold">default</span>
                <?php else: ?>
                <a href="<?php ph($this->adminBaseUrl()) ?>?a=payment.setDefault&amp;id=<?php ph($method->id) ?>">Set as default</a>
                <?php endif  ?>
            </td>
            <td>
                <a href="<?php ph($this->adminBaseUrl()) ?>?a=payment.edit&amp;id=<?php ph($method->id) ?>">Edit</a>
            </td>
        </tr>
<?php endforeach; ?>
    </tbody>
</table>
<?php $this->paginate() ?>
<?php endif ?>