<?php
/**
 * @package sess
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
?>

<table class="records" cellspacing="0">
    <thead>
        <tr>
            <td>ID</td>
            <td>Date</td>
            <td>SID</td>
            <td>Actions</td>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($this->sessions as $session): ?>
        <?php
        $idle = time() - $session->modify_date;
        if ($idle < 60 * 2) { $color = "#ff8"; }
        else if ($idle < 60 * 10) { $color = "#fc8"; }
        else { $color = ""; }
        ?>
        <tr style="background-color: <?php ph($color) ?>">
            <td><?php ph($session->id) ?></td>
            <td><?php ph(mm_datetime($session->creation_date)) ?></td>
            <td><?php ph('...' . substr($session->sid, -8)) ?></td>
            <td><?php echo $this->linkTag('view', '?a=sess_admin.show&id=' . $session->id) ?></td>
        </tr>
        <?php endforeach ?>
    </tbody>
</table>
