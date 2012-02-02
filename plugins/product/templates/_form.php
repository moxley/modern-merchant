<?php
/**
 * @package product
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
?>

<?php /* TODO: Factor out this javascript (waiting on ticket #132) */ ?>

<input type="hidden" name="original_sku" value="<?php ph($this->product->sku) ?>" />
<?php if( isset($this->transition) ) { ?>
<input type="hidden" name="transition" value="<?php ph($this->transition) ?>" />
<?php } ?>

<table width="100%" border="0" cellpadding="0" cellspacing="0">
    <tr class="row">
        <td class="layout itemRowTitle">
            <div class="formRowTitle">Name</div>
        </td>
        <td class="layout itemRowValues">
            <?php echo $this->textField('product[name]', array('size' => 60, 'class' => 'big')); ?>
        </td>
    </tr>
    <tr class="row">
        <td class="layout itemRowTitle">
            <div class="formRowTitle">SKU</div>
        </td>
        <td class="layout itemRowValues">
            <?php echo $this->textField('product[sku]', array('size' => 15, 'class' => $this->product->id ? '' : 'disabled', 'disabled' => ($this->product->id ? false : true)))?>
            <?php if (!$this->product->id) : ?>
            <input type="hidden" name="product[sku_same_as_id]" value="0" />
            <?php echo $this->checkBox('product[sku_same_as_id]', '1', array('onclick' => "ProductEdit.onSkuClick()", 'style' => 'margin-left: 20px')); ?>
            <label for="product_sku_same_as_id_1">When checked, product's SKU will be the same as the product ID</label>
            <?php endif; ?>
        </td>
    </tr>
    <tr class="row">
        <td class="layout" valign="top" width="150">
            <div class="formRowTitle">
            Categories
            </div>
        </td>
        <td class="layout itemRowValues">
            <div style="margin-bottom: 20px">
                <?php $this->mm_printParentSelect(); ?>
                <a href="javascript:void(CategorySelections.add('product[category_ids][]'))">Add to category...</a>
                <?php $this->mm_printCategorySelectionsCall('product[category_ids][]', $this->product->categories); ?>
            </div>
        </td>
    </tr>
    <tr class="row">
        <td class="layout itemRowTitle">
            <div class="formRowTitle">Description</div>
            Description must be valid HTML
        </td>
        <td class="layout itemRowValues">
            <?php echo $this->textArea('product[description]', array('size'=>'80x5', 'wrap'=>'virtual')); ?>
            <div>
                <a href="#" onclick="Admin.textAreaTaller('product[description]');return false">taller</a>
                <a href="#" onclick="Admin.textAreaShorter('product[description]');return false">shorter</a>
            </div>
        </td>
    </tr>
    <tr class="row">
        <td class="layout itemRowTitle">
            <div class="formRowTitle">Keywords</div>
            (separate with commas)
        </td>
        <td class="layout itemRowValues">
            <?php echo $this->textField('product[keywords]', array('size' => 60, 'class' => 'big')); ?>
        </td>
    </tr>
    <?php if ($this->product->id): ?>
    <tr class="row">
        <td class="layout itemRowTitle">
            <div class="formRowTitle">ID</div>
        </td>
        <td class="layout itemRowValues">
        <?php ph($this->product->id) ?>
        </td>
    </tr>
    <tr class="row">
        <td class="layout itemRowTitle">
            <div class="formRowTitle">Modification Date</div>
        </td>
        <td class="layout itemRowValues">
        <?php ph(mm_date($this->product->modify_date)) ?>
        </td>
    </tr>
    <?php endif ?>
    <tr class="row">
        <td class="layout itemRowTitle">
            <div class="formRowTitle">Modification User</div>
        </td>
        <td class="layout itemRowValues">
        <?php ph($this->product->modify_username) ?>
        </td>
    </tr>
    <tr class="row">
        <td class="layout itemRowTitle">
            <div class="formRowTitle">Active</div>
        </td>
        <td class="layout itemRowValues">
            <?php echo $this->checkBox('product[active]')?>
        </td>
    </tr>
    <tr class="row">
        <td class="layout itemRowTitle">
            <div class="formRowTitle">Date Available</div>
        </td>
        <td class="layout itemRowValues">
            <?php echo $this->textField('product[available_on]', array('filter' => 'mm_date'))?>
            (MM/DD/YY, MM/DD/YYYY, or YYYY-MM-DD)<br />
            Product will not be shown until Date Available, or leave blank to make it available any time.
        </td>
    </tr>
    <tr class="row">
        <td class="layout itemRowTitle">
            <div class="formRowTitle">Sort Order</div>
        </td>
        <td class="layout itemRowValues">
            <?php echo $this->textField('product[sortorder]', array('size' => 3))?>
        </td>
    </tr>
    <tr class="row">
        <td class="layout itemRowTitle">
            <div class="formRowTitle">In-stock quantity</div>
        </td>
        <td class="layout itemRowValues">
            <?php echo $this->textField('product[count]', array('size' => 4)); ?>
            Leave blank to signify an unlimited quantity.
        </td>
    </tr>
    <tr class="row">
        <td class="layout itemRowTitle">
            <div class="formRowTitle">Price</div></td>
        <td class="layout itemRowValues">
            <?php echo $this->textField('product[price]', array('size' => 10)); ?>
            <i>Note: </i>This price may be overridden from the Pricing interface.
        </td>
    </tr>
    <tr class="row">
        <td class="layout itemRowTitle">
            <div class="formRowTitle">Weight</div></td>
        <td class="layout itemRowValues">
            <?php echo $this->textField('product[weight]', array('size' => 10)); ?>
        </td>
    </tr>
    <tr class="row">
        <td class="layout itemRowTitle">
            <div class="formRowTitle">Images</div>
            <p>The first image will be the thumbnail image.</p>
        </td>
        <td class="layout itemRowValues">
            <?php foreach ($this->product->images as $this->image): ?>
            <div class="image_row">
                <?php $this->render('product/_image'); ?>
            </div>
            <?php endforeach; ?>
            
            <div id="new_image_rows">
            </div>

            <div id="new_image_template" style="display:none" class="image_row">
                <?php $this->image = new media_Media(array('owner_id' => $this->product->id, 'owner_type' => 'product_Product', 'sortorder' => -999)); ?>
                <?php $this->render('product/_image'); ?>
            </div>
            
            <div>
                <a href="#" onclick="ImageManager.addImageField(); return false;">Add image...</a>
            </div>
        </td>
    </tr>
    <tr class="row">
        <td class="layout itemRowTitle">
            <div class="formRowTitle">Notes</div>
        </td>
        <td class="layout itemRowValues">
            <?php echo $this->textArea('product[comment]', array('size'=>'50x5', 'wrap'=>'virtual')); ?>
        </td>
    </tr>
</table>
