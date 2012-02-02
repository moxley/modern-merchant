<?php
/**
 * @package category
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

$siteArea = $this->home;
?>

<div class="contentRow">

    <div class="mainContentArea product-detail">

        <div class="continue-shopping">
            <a href="<?php $this->writeUrl(array('name'=>'catalog.product_list')); ?>" onclick="history.go(-1); return false;">Continue shopping</a>
        </div>

        <div id="product-detail">

            <!-- Images -->
            <div class="images">
                <?php $this->writeProductDetailImages(array('product' => $this->product)); ?>
            </div>

            <div class="information">
                <h2 class="title"><?php ph($this->product->name); ?> (<?php ph($this->product->sku); ?>)</h2>
            
                <?php if($this->isAdmin()): ?>
                <div class="edit">
                    <a href="<?php $this->writeUrl(array('name'=>'admin.script')); ?>?action=product.edit&amp;id=<?php ph($this->product->id); ?>">edit</a>
                </div>
                <?php endif; ?>
            
                <div class="description">
                    <?php echo $this->product->description ?>
                </div>

                <?php if ($this->product->is_for_sale): ?>
                <!-- BEGIN: add-to-cart button -->
                <form class="add-to-cart" method="POST" action="<?php $this->writeUrl(array('type'=>'addToCart', 'sku'=>$this->product->sku)); ?>">
                    <?php if ($this->product->percent_off): ?>
                    <div class="was">Was: <span class="price">$<?php ph($this->product->price) ?></span></div>
                    <div class="save">Save <span class="percent"><?php ph($this->product->percent_off) ?>%</span></div>
                    <?php endif ?>

                    <div class="price adjusted-price">$<?php ph($this->product->adjusted_price); ?></div>
                    <?php echo $this->submitButton("Add to Cart", array('class' => 'button add-to-cart')) ?>
                </form>
                <!-- END: add-to-cart button -->
                <?php else: ?>
                <div class="not-for-sale"><strong>Not for sale</strong></div>
                <?php endif; ?>
            </div>
            
        </div><!-- end: product-detail -->

        <div class="continue-shopping">
            <a href="<?php $this->writeUrl(array('name'=>'catalog.product_list')); ?>" onclick="history.go(-1); return false;">Continue shopping</a>
        </div>

    </div><!-- end: mainContentArea -->
</div><!-- end: contentRow -->
