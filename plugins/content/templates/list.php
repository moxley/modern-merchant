<?php
/**
 * @package content
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
?>
<table class="records">
    <thead>
        <tr>
            <td>Name</td>
            <td>Description</td>
            <td>Type</td>
            <td>Actions</td>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($this->items as $content): ?>
        <tr>
            <td><?php ph($content->name) ?></td>
            <td><?php ph($content->description) ?></td>
            <td><?php ph($content->type) ?></td>
            <td>
                <a href="?a=content.edit&amp;id=<?php ph($content->id) ?>">edit</a>
                <a href="?a=content.delete&amp;id=<?php ph($content->id) ?>" onclick="return confirm('Are you sure?')">delete</a>
            </td>
        </tr>
        <?php endforeach ?>
    </tbody>
</table>
