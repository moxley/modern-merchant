<?php
/**
 * @package user
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
?>
<?php if (!$this->users): ?>
<div class="no_records_found">No records found</div>
<?php else: ?>
<table cellspacing="0" class="records">
    <thead>
        <tr> 
            <td>#</td>
            <td>Username</td>
            <td>First Name</td>
            <td>Last Name</td>
            <td>Access</td>
            <td>Action</td>
        </tr>
    </thead>
    <tbody>
        <?php
        $rowClasses = array("dataRowEven", "dataRowOdd");
        foreach ($this->users as $r=>$user):
            $class = $rowClasses[ ($r+1)%2 ];
        ?>
        <tr class="<?php print $class ?>"> 
            <td><?php ph($user->record_number) ?></td>
            <td><?php ph($user->username) ?></td>
            <td><?php ph($user->first_name); ?></td>
            <td><?php ph($user->last_name); ?></td>
            <td><?php ph(implode(', ', $user->access_names)); ?></td>
            <td>
                <a href="<?php ph($this->adminBaseUrl()) ?>?action=user.edit&amp;id=<?php print i($user->id); ?>"
                    title="Edit this user">Edit</a>
                <a href="<?php ph($this->urlFor(array('a' => 'user.delete', 'id' => $user->id))); ?>"
                    onclick="return confirm('Are you sure?')"
                    title="Delete this user">Delete</a>
            </td>
        </tr>
        <?php endforeach ?>
    </tbody>
</table>
<?php endif ?>
