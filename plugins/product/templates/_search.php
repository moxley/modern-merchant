<?php
/**
 * @package product
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
?>
<form class="listForm" name="form1" method="get" action="<?php ph($this->adminBaseUrl()) ?>">
    <input type="hidden" name="a" value="product.search" />

    <div class="body">
        <!-- Search box -->
        <div style="float:left; margin-right: 10px;">
            <?php echo $this->textField('q', array('size' => 30, 'style' => 'border:1px solid black')); ?>
            <input type="submit" title="Search" class="submit" value="Search" />
        </div>
    
        <!-- Check boxes -->
        <?php if (false): ?>
        <div class="searchInBox">
            <span style="margin-right: 5px; font-weight: bold">Search in:</span>

            <input type="checkbox" id="search_in[product.sku]" name="search_in[product.sku]" 
                value="1" <?php print (!isset($output["sku"]) || $output['sku']) ? 'checked="checked"':'' ?> />
            <label for="search_in[product.sku]" style="margin: 0 10px 0 2px">SKU</label>&nbsp;
        
            <input type="checkbox" id="search_in[product.name]" name="search_in[product.name]"
                value="1" <?php print (!isset($output["Product.Name"]) || $output['Product.Name']) ? 'checked="checked"':"" ?> />
            <label for="search_in[product.name]" style="margin: 0 10px 0 2px">Name</label>&nbsp; 
        
            <input type="checkbox" id="search_in[product.description]" name="search_in[product.description]"
                value="1" <?php print gv($output, "Product.Description") ? 'checked="checked"':"" ?> />
            <label for="search_in[product.description]" style="margin: 0 10px 0 2px">Description</label>&nbsp;
        </div>
        <?php endif; ?>
    </div>
</form>
