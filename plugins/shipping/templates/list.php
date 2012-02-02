<?php
/**
 * Template used to list shipping types.
 *
 * @package shipping
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
?>
<?php if (!$this->shipping_methods ): ?>
<div class="no_records_found">No records found</div>
<?php else: ?>
<?php $this->paginate() ?>
<table cellspacing="0" class="records">
    <thead>
        <tr>
            <td>ID</td>
            <td>Method</td>
            <td>Active</td>
            <td>Default</td>
            <td>Sort</td>
            <td>Action</td>
        </tr>
    </thead>
    <tbody>
        <?php
        $rowClasses = array("dataRowEven", "dataRowOdd");
        foreach ($this->shipping_methods as $r=>$method):
            $class = $rowClasses[ ($r+1)%2 ];
        ?>
        <tr class="<?php print $class ?>"> 
            <td><?php ph($method->id) ?></td>
            <td><?php ph($method->name) ?></td>
            <td>
                <?php ph($method->active_title) ?>
                <?php if( $method->active ): ?>
                <a href="<?php ph($this->adminBaseUrl()) ?>?a=shipping.deactivate&amp;id=<?php ph($method->id) ?>">Deactivate</a>
                <?php else: ?>
                <a href="<?php ph($this->adminBaseUrl()) ?>?a=shipping.activate&amp;id=<?php ph($method->id) ?>">Activate</a>
                <?php endif ?>
            </td>
            <td>
                <?php ph($method->default_title) ?>
                <?php if( !$method->is_default ): ?>
                <a href="<?php ph($this->adminBaseUrl()) ?>?a=shipping.setDefault&amp;id=<?php ph($method->id) ?>">Set as default</a>
                <?php else: ?>
                Default
                <?php endif ?>
            </td>
            <td>
                <?php ph($method->sortorder) ?>
            </td>
            <td>
                <a href="<?php ph($this->adminBaseUrl()) ?>?a=shipping.edit&amp;id=<?php ph($method->id) ?>">Edit</a>
                <a href="<?php ph($this->urlFor(array('a' => 'shipping.delete', 'id' => $method->id))) ?>)" onclick="return confirm('Are you sure?')">Delete</a>
            </td>
        </tr>
        <?php endforeach ?>
    </tbody>
</table>
<?php $this->paginate() ?>
<?php endif ?>
