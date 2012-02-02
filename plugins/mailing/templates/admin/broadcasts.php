<?php if (!$this->broadcasts): ?>
<p>There are no broadcasts.</p>
<?php else: ?>
<table cellspacing="0" class="records">
    <thead>
        <tr>
            <td>Name</td>
            <td>Started Time</td>
            <td>End Time</td>
            <td>Actions</td>
        </tr>
    </thead>
    <tbody>
<?php foreach ($this->broadcasts as $broadcast): ?>
    <tr>
        <td><?php ph($broadcast->name) ?></td>
        <td><?php ph(mm_datetime($broadcast->started_on)) ?></td>
        <td><?php ph(mm_datetime($broadcast->completed_on)) ?></td>
        <td>
            <?php echo $this->linkTag('details', array('a' => 'mailing_admin.broadcastDetails', 'id' => $broadcast->id)) ?>
            <?php echo $this->linkTag('delete', array('a' => 'mailing_admin.deleteBroadcast', 'id' => $broadcast->id), array('onclick' => "return confirm('Are you sure?')")) ?>
        </td>
    </tr>
<?php endforeach ?>
    </tbody>
</table>
<?php endif ?>
