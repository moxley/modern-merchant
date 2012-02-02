<?php
/**
 * @package category
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
?>
<div class="products contentRow mainContentArea">
    <div class="header"></div>
    
    <?php if ($this->category): ?>
    <div class="category">
        <?php if ($this->category->image && $this->category->image->filename): ?>
            <img src="<?php ph($this->urlForImage($this->category->image)); ?>"
                width="<?php ph($this->category->image->width); ?>"
                height="<?php ph($this->category->image->height); ?>"/>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <div class="resultsNav top">
        <?php $this->paginate(); ?>
    </div>

<?php $products = $this->products; ?>
<?php if ($products): ?>
    <div class="products">
    <table border="0" cellpadding="5" cellspacing="0" class="productTable">

<?php
foreach ($products as $i=>$product):
?>
<?php if ($i%2): ?>
<!-- ODD COLUMN -->
<?php else: ?>
<!-- EVEN COLUMN -->
        <tr>
<?php endif; ?>
            <!-- Product image -->
            <td width="150" valign="top" class="product image product-image">
                <a href="<?php $this->writeUrl(array('type'=>'productDetail', 'sku'=>$product->sku)); ?>">
                    <?php echo $this->productThumb($product); ?>
                </a>
            </td>

            <!-- Main Product attributes -->
            <td valign="top" class="product description"> 
                    <!-- Product Title -->
                    <div class="title itemDescTitle">
                        <a href="<?php $this->writeUrl(array('type'=>'productDetail', 'sku'=>$product->sku)); ?>">
                        <?php ph($product->name); ?>
                        </a>
                    </div>

<?php if ($product->adjusted_price > 0): ?>
                <?php if ($product->percent_off): ?>
                <div class="was">Was: <span class="price">$<?php ph($product->price) ?></span></div>
                <div class="save">Save <span class="percent"><?php ph($product->percent_off) ?>%</span></div>
                <?php endif ?>
                <div class="price itemDescPrice adjusted-price">
                    $<?php ph($product->adjusted_price); ?>
                </div>
                
                <!-- Add to cart button -->
                <form method="POST" action="<?php $this->writeUrl(array('type'=>'addToCart', 'sku'=>$product->sku)); ?>">
                    <input type="submit" value="Add to Cart" class="button add-to-cart" />
                </form>
<?php else: ?>
                <p><span class="itemDescPrice">Not for Sale</span></p>
<?php endif; ?>

                <div class="detail_link">
                    <a href="<?php $this->writeUrl(array('type'=>'productDetail', 'sku'=>$product->sku)); ?>"
                    class="detailLink">Click for detail</a>
                </div>

                <?php if($this->isAdmin()): ?>
                <!-- Edit link for administrator -->
                <a href="<?php ph(mm_getConfigValue('urls.admin.script')); ?>?action=product.edit&amp;id=<?php ph($product->id); ?>">edit</a>
                <?php endif; ?>

            </td>

<?php if ($i%2): ?></tr><?php endif; ?>

<?php endforeach; ?>

<?php if ($i%2): ?>
            <!-- EMPTY CELL -->
            <td width="150" align="center" valign="top">&nbsp;</td>
            <td valign="top">&nbsp;</td>
        </tr>
<?php endif; ?>
        <tr>
            <td colspan="4">
            <div class="resultsNav bottom">
                <?php $this->paginate(); ?>
            </div>
    
            </td>
        </tr>
    </table>
    </div><!-- #products -->
<?php else: ?> <?php /* No products */ ?>
    <?php if ($this->req('q')): ?>
        <?php if ($this->category): ?>
        No matching products found in <a href="?a=catalog.products&amp;category_id=<?php ph($this->category->id) ?>"><?php ph($this->category->name); ?></a>.
        <?php else: ?>
        No matches for '<?php ph($this->q); ?>'
        <?php endif; ?>
    <?php else: ?>
        <?php if ($this->category): ?>
        There are no products in "<?php ph($this->category->name); ?>".
        <?php else: ?>
        No products found for '<?php ph($this->q); ?>'
        <?php endif; ?>
    <?php endif; ?>
<?php endif; ?>

    <div class="footer"></div>
</div><!-- end: mainContentArea, contentRow -->
