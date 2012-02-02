<?php
/**
 * Template used to list global settings.
 *
 * @package setting
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

?>
<?php
if (!$this->settings):
?>
<div class="no_records_found">No records found</div>
<?php
else:
?>
<?php $this->paginate() ?>
<table cellspacing="0" class="records">
    <thead>
        <tr> 
            <td>#</td>
            <td>Name</td>
            <td>Value</td>
            <td>Action</td>
        </tr>
    </thead>
    <tbody>
    <?php
    $rowClasses = array("dataRowEven", "dataRowOdd");
    foreach ($this->settings as $r=>$setting):
    $class = $rowClasses[ ($r+1)%2 ];
    ?>
        <tr class="<?php print $class ?>">
            <td><?php print $r+1 ?></td>
            <td><?php ph($setting->name) ?></td>
            <td><?php ph($setting->value) ?></td>
            <td align="center">
                <a href="<?php ph($this->adminBaseUrl()) ?>?action=setting.edit&amp;id=<?php ph($setting->id) ?>">Edit</a>
            </td>
        </tr>
<?php
    endforeach;
?>
</table>
<?php endif ?>
<?php $this->paginate() ?>
