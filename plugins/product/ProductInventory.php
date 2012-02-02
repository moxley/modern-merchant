<?php
/**
 * @package product
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * Interface to the Product Inventory database.
 */
class product_ProductInventory
{
    public $failedSkus;
    
    /**
     * Adjusts inventory based on a shopping cart.
     */
    function subtractCart(&$cartObj)
    {
        if( ! is_object($cartObj) )
        {
            die("ProductInventory:subtractCart(): \$cartObj is not an object");
        }
        $list = $this->cartToList($cartObj);
        return $this->subtractList($list);
    }

    /**
     * @depreciated Use cart_Cart::getSkuQuantities()
     * @return array  A hash containing the counts of each sku in the cart. Hash format
     *                is sku=>qty.
     */
    function &cartToList(&$cartObj)
    {
        return $cartObj->getSkuQuantities();
    }

    /**
     * Adjust inventory.
     *
     * @param array  A hash containing each sku and its quantity to remove from inventory (sku=>qty)
     */
    function subtractList(&$counts)
    {
        // Array to keep track of skus that failed their update
        $badSkus = array();

        foreach( $counts as $sku=>$qty ) {
            if( !$this->subtract($sku, $qty) ) $badSkus[] = $sku;
        }

        if( $badSkus ) {
            $this->failedSkus = $badSkus;
            return FALSE;
        }

        return TRUE;
    }

    function subtract($sku, $qty)
    {
        // Fetch a database connection
        $dbh =& mm_getDatabase();

        if( $qty < 1 ) return FALSE;
        $query = "UPDATE mm_product SET count = count - " . intval($qty) . " WHERE sku=".dq($sku);
        $res = $dbh->execute($query);
        return ($dbh->affectedRows() ? TRUE : FALSE);
    }

    /**
     * Get an inventory count for a given SKU
     */
    function getCount($sku)
    {
        $dbh = mm_getDatabase();
        $query = "SELECT count FROM mm_product WHERE sku=?";
        return $dbh->getOne($query, array($sku));
    }
        
    /**
     * Get inventory counts for a list of SKUs.
     *
     * @array  An hash of SKU counts where each key is the SKU.
     */
    function getCountList($skuList)
    {
        $dbh = mm_getDatabase();

        $cleanList = array();
        foreach( $skuList as $sku ) {
            $cleanList[] = dq($sku);
        }
            
        $query = "SELECT sku,count FROM mm_product WHERE sku IN (" . implode(',', $cleanList) . ")";
        $res = $dbh->query($query);

        // Initialize the inventory counts to zero
        $counts = array();
        foreach( $skuList as $sku=>$count ) {
            $counts[$sku] = 0;
        }
            
        while ($record = $res->fetchAssoc()) {
            $counts[$record['sku']] = $record['count'];
        }
        $res->free();
        return $counts;
    }
}
