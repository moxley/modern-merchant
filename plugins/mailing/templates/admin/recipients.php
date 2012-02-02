<?php if (!$this->recipients): ?>
<p>There are no recipients.</p>
<?php else: ?>
<table cellpadding="0" class="records">
    <thead>
        <tr>
            <td>ID</td>
            <td>Email</td>
            <td>Name</td>
            <td>Add Date</td>
            <td>Customer ID</td>
            <td>Actions</td>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($this->recipients as $recipient): ?>
        <tr>
            <td>
                <?php ph($recipient->id) ?>
            </td>
            <td>
                <?php ph($recipient->email) ?>
            </td>
            <td>
                <?php ph($recipient->name) ?>
            </td>
            <td>
                <?php ph(mm_date($recipient->created_on)) ?>
            </td>
            <td>
                <?php if ($recipient->customer_id): ?>
                <?php echo $this->linkTag(h($recipient->customer_id), array('a' => 'customer_admin.edit', 'id' => $recipient->customer_id)) ?>
                <?php endif ?>
            </td>
            <td>
                <?php echo $this->linkTag('edit', array('a' => 'mailing_admin.editRecipient', 'id' => $recipient->id)) ?>
                <?php echo $this->linkTag('delete', array('a' => 'mailing_admin.deleteRecipient', 'id' => $recipient->id), array('onclick' => "return confirm('Are you sure?')")) ?>
            </td>
        </tr>
        <?php endforeach ?>
    </tbody>
</table>
<?php endif ?>
