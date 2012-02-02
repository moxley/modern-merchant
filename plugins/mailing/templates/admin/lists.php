<?php if ($this->lists): ?>
<table cellspacing="0" class="records">
    <thead>
        <tr>
            <td>List Name</td>
            <td>List Size</td>
            <td>Public?</td>
            <td>Creation Date</td>
            <td>Action</td>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($this->lists as $list): ?>
        <tr>
            <td class="first">
                <?php ph($list->name) ?>
            </td>
            <td>
                <?php ph($list->size) ?>
            </td>
            <td>
                <?php echo $list->is_public ? 'Yes' : 'No' ?>
            </td>
            <td>
                <?php echo mm_date($list->created_on) ?>
            </td>
            <td>
                <?php echo $this->linkTag("edit", array('a' => 'mailing_admin.editList', 'id' => $list->id)) ?>
                <?php echo $this->linkTag("delete", array('a' => 'mailing_admin.deleteList', 'id' => $list->id), array('onclick' => "return confirm('Are you sure?')")) ?>
            </td>
        </tr>
        <?php endforeach ?>
    </tbody>
</table>
<?php else: ?>
<p>There are no lists.</p>
<?php endif ?>
