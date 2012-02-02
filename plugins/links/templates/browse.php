<h1>
    Links
    <?php if ($this->category) echo ": " . h($this->category->name) ?>
</h1>

<h3>Browse Categories</h3>

<?php $this->render('links/_categories') ?>

<?php if ($this->links): ?>
<table class="links" cellpadding="0">
<?php foreach ($this->links as $link): ?>
    <tr class="link">
        <td class="image_block">
            <a href="<?php ph($this->urlFor(array('a' => 'links.click', 'id' => $link->id))) ?>">
                <?php
                if ($link->image) {
                    echo $this->imageTag($link->image->url_path, array('width' => $link->image->width, 'height' => $link->image->height, 'alt' => $link->url));
                }
                else {
                    ph($link->url);
                }
                ?>
            </a>
        </td>
        <td class="description_block">
            <a href="<?php ph($this->urlFor(array('a' => 'links.click', 'id' => $link->id))) ?>" class="name"><?php ph($link->business_name)?></a>
            <p class="description"><?php ph($link->description)?></p>
        </td>
    </tr>
<?php endforeach ?>
</table>
<?php else: ?>
<?php
if ($this->category) {
    echo "<p>There are no links in this category.</p>";
}
else {
    echo "<p>There are no links at this time.</p>";
}
?>
<?php endif ?>
