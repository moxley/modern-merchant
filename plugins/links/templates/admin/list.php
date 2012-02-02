<table cellspacing="0" class="records">
    <thead>
        <tr>
            <td>Approved?</td>
            <td>Business Name</td>
            <td>Clicks</td>
            <td>Category</td>
            <td>Image</td>
            <td>Action</td>
        </tr>
    </thead>
    <tbody>
<?php foreach ($this->links as $link): ?>
        <tr>
            <td><?php ph($link->approved_text) ?></td>
            <td><?php ph($link->business_name) ?></td>
            <td><?php ph($link->counter) ?></td>
            <td>
                <?php if ($link->category): ?>
                <?php ph($link->category->name) ?>
                <?php endif ?>
            </td>
            <td>
                <?php if ($link->image): ?>
                <img src="<?php ph($link->image->url_path) ?>" alt="<?php ph($link->name) ?>" width="<?php ph($link->image->width) ?>" height="<?php ph($link->image->height) ?>" />
                <?php endif ?>
            </td>
            <td>
                <a href="<?php ph($this->urlFor(array('a' => 'links_admin.edit', 'id' => $link->id))) ?>">edit</a>
                <a href="<?php ph($this->urlFor(array('a' => 'links_admin.delete', 'id' => $link->id))) ?>" onclick="return confirm('Are your sure?')">delete</a>
            </td>
        </tr>
<?php endforeach ?>
    </tbody>
</table>
