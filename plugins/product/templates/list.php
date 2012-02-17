<?php
/**
 * @package product
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
?>
<div id="page_product_list">
<div id="category_column">
<?php
$this->render("product/categories");
?>
</div>

<div id="products_column">

    <div class="pageHeader">
        <h2>Products<?php if(@$this->category) ph(' - '.$this->category->name) ?></h2>

        <?php $this->render('product/_search'); ?>

        <div class="addNewProduct">
            <button onclick="window.location='?a=product.new&amp;category_id=<?php ph($this->category_id) ?>'">Add New Product</button>
        </div>
    </div>

    <?php
    if( !$this->products ):
    ?>
    <div class="no_records_found">No records found</div>
    <?php
    else:
    ?>
    <form method="post" name="itemList" action="?a=product.updateMultiple&amp;return=<?php ph(urlencode($_SERVER['REQUEST_URI'])); ?>">
        <div>
            <?php $this->paginate() ?>
            <table cellpadding="0" class="records">
                <thead>
                    <tr> 
                        <td>#</td>
                        <td>
                            <?php echo $this->sortLink('sku', 'SKU') ?>
                        </td>
                        <td>
                            <?php echo $this->sortLink('sortorder', 'Sort') ?>
                        </td>
                        <td>
                            <?php echo $this->sortLink('name', 'Name') ?>
                        </td>
                        <td class="image">Image</td>
                        <td>
                            <?php echo $this->sortLink('price', 'Price') ?>
                        </td>
                        <td>
                            <?php echo $this->sortLink('created_on', 'Date Created') ?>
                        </td>
                        <td>
                            <?php echo $this->sortLink('modify_date', 'Date Modified') ?>
                        </td>
                        <td>
                            <?php echo $this->sortLink('available_on', 'Date Available') ?>
                        </td>
                        <td>
                            <?php echo $this->sortLink('count', 'Qty') ?>
                        </td>
                        <td>Active</td>
                        <td>Actions</td>
                        <td>Delete</td>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $rowClasses = array("dataRowEven", "dataRowOdd");

                    foreach ($this->products as $r=>$product):
                        $class = $rowClasses[ ($r+1)%2 ];
                    ?>
                    <tr class="<?php print $class ?>"> 
                        <td class="first"><?php ph($r+1) ?></td>
                        <td><?php ph($product->sku) ?></td>
                        <td>
                            <input type="text" name="products[<?php ph($product->id) ?>][sortorder]" value="<?php ph($product->sortorder) ?>" size="3" class="number" style="width: 95%" />
                        </td>
                        <td>
                            <input type="text" name="products[<?php ph($product->id) ?>][name]" value="<?php ph($product->name) ?>" size="25" style="width: 95%" />
                        </td>
                        <td class="image">
                        <?php if ($product->images): ?>
                            <?php echo $this->productThumbnailTag($product->images[0]); ?>
                        <?php endif ?>
                        </td>
                        <td>
                            <input type="text" name="products[<?php ph($product->id) ?>][price]" value="<?php ph($product->price) ?>" size="5" class="number" style="width: 95%"/>
                        </td>
                        <td>
                            <?php ph(date('Y-m-d', $product->created_on)) ?>
                        </td>
                        <td>
                            <?php ph(date('Y-m-d', $product->modify_date)) ?>
                        </td>
                        <td>
                            <?php ph(date('Y-m-d', $product->available_on)) ?>
                        </td>
                        <td>
                            <input type="text" name="products[<?php ph($product->id) ?>][count]" value="<?php ph($product->count) ?>" size="3" class="number" />
                        </td>
                        <td>
                            <input type="checkbox" name="products[<?php ph($product->id) ?>][active]" <?php print $product->active ? 'checked' : '' ?> />
                        </td>
                        <td>
                            <a href="?action=product.edit&amp;id=<?php ph($product->id) ?>">Edit</a>
                            <a href="javascript:void(deleteProduct('<?php ph($product->id) ?>'));">Delete</a>
                        </td>
                        <td>
                            <input type="checkbox" name="products[<?php ph($product->id) ?>][delete]" value="1"/>
                        </td>
                    </tr>
                    <?php
                        endforeach;
                    ?>
                </tbody>
            </table>
            <div class="buttons">
                <input type="submit" value="update" />
            </div>
            
            <?php $this->paginate() ?>
        </div>
      </form>
<?php endif; ?>

</div><!-- END #products_column -->
</div><!-- end #page_product_list -->
