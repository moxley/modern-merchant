<?php
/**
 * @package product
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

if ($this->image->id) {
    $label = "Image " . ($this->image->sortorder + 1);
}
else {
    $label = "New Image";
}
?>
<div class="image-edit">
    <h3><?php ph($label); ?></h3>
    <div class="row-body">
        <?php if ($this->image->id):?>
        <div style="float: left; margin: 0 10px 10px 0">
            <?php echo $this->productThumbnailTag($this->image, array('no_cache' => true)); ?><br />
            <!-- Click image to see full size -->
        </div>
        <?php endif ?>

        <?php if ($this->image->id):?>
        <div class="row row-first">
            <div><b>Dimensions:</b> <?php ph("{$this->image->width} x {$this->image->height} pixels"); ?>
            <div><b>Image Tag:</b> <code><?php ph("<img src=\"" . h($this->image->url_path) . "\" width=\"{$this->image->width}\" height=\"{$this->image->height}\" />") ?></code></div>
        </div>
        <?php endif ?>
        
        <div class="row<?php echo ($this->image->id > 0) ? '' : ' row-first' ?>">
            Click the 'Browse...' button to upload a new image<br />
            <?php echo $this->productImageFileField($this->image); ?>
        </div>
        
        <?php if ($this->image->id > 0):?>
        <div class="row">
            <?php echo $this->checkBoxTag("product[images_to_delete][{$this->image->id}]", '1', false); ?>
            <label for="product_images_to_delete__<?php echo $this->image->id; ?>">
                Click to remove this image</label>
        </div>
        <?php endif ?>

    </div><!-- END: row-body -->
</div><!-- END: row -->

