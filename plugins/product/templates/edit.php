<?php
/**
 * @package product
 * @subpackage templates
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
?>
<div id="product_edit">
    <?php $this->render('product/_head'); ?>
    <form id="product_edit_form" name="product_edit_form" method="post" action="?a=product.update" enctype="multipart/form-data">
        <div>
            <input type="hidden" name="id" value="<?php ph($this->product->id) ?>" />
<?php
$this->render('product/_buttons');
$this->render('product/_form');
$this->render('product/_buttons');
?>
        </div>
    </form>
</div>
