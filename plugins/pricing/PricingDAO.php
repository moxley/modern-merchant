<?php
/**
 * @package pricing
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * @package pricing
 */
class pricing_PricingDAO
{
    /*  CREATE TABLE mm_pricing (
          id int not null auto_increment,
          name varchar(30),
          type enum('multiply', 'add', 'override') not null default 'multiply',
          value decimal(6,4) not null default 0.0,
          primary key (id))
    */
    
    function add($pricing)
    {
        $dbh = mm_getDatabase();
        $fmt = $dbh->getFormatter();
        $trim = trim($pricing->name);
        if ($trim != $pricing->name) $pricing->name = $trim;
        $sql = sprintf("INSERT INTO mm_pricing (name, type, value) VALUES (" .
                "%s, %s, %s)",
                $fmt->fString($pricing->name),
                $fmt->fString($pricing->type),
                $fmt->fString($pricing->value));
        $dbh->execute($sql);
        $pricing->id = $dbh->lastInsertId();
    }
    
    function update($pricing)
    {
        $dbh = mm_getDatabase();
        $fmt = $dbh->getFormatter();
        $trim = trim($pricing->name);
        if ($trim != $pricing->name) $pricing->name = $trim;
        $sql = "UPDATE mm_pricing " .
                "SET name=" . $fmt->fString($pricing->name) .
                ",type=" . $fmt->fString($pricing->type) .
                ",value=" . $fmt->fFloat($pricing->value) .
                " WHERE id=" . $fmt->fInt($pricing->id);
        $dbh->query($sql);
    }
    
    function delete($pricing)
    {
        $this->deleteById($pricing->id);
    }
    
    function deleteById($id)
    {
        $sql = "DELETE FROM mm_pricing_category WHERE pricing_id=" . (int) $id;
        mm_getDatabase()->query($sql);
        $sql = "DELETE FROM mm_pricing WHERE id=" . (int) $id;
        mm_getDatabase()->query($sql);
    }
    
    function deleteCategoryIdsFromPricing($pricing, $category_ids)
    {
        if (!$category_ids) return;
        $dbh = mm_getDatabase();
        $fmt = $dbh->getFormatter();
        $sql = "DELETE FROM mm_pricing_category WHERE pricing_id=" .
            $fmt->fInt($pricing->id);
        $sql .= ' AND category_id in (' . cleanIntList($category_ids) . ')';
        $dbh->query($sql);
    }
    
    function getCount()
    {
        $dbh = mm_getDatabase();
        return (int) $dbh->getOne('select count(id) from mm_pricing');
    }
    
    function deleteAll()
    {
        $dbh = mm_getDatabase();
        $dbh->query('delete from mm_pricing');
    }
    
    function fetch($id)
    {
        $dbh = mm_getDatabase();
        $fmt = $dbh->getFormatter();
        $sql = "select" .
                " id, name, type, value" .
                " from mm_pricing" .
                " where id=" . $fmt->fInt($id);
        $row = $dbh->getOneAssoc($sql);
        if (!$row) return null;
        return $this->parseRow($row);
    }
    
    function parseRow($row)
    {
        $fmt = mm_getDatabase()->getFormatter();
        $pricing = new pricing_Pricing;
        $pricing->id = $fmt->pInt($row['id']);
        $pricing->name = $row['name'];
        $pricing->type = $row['type'];
        $pricing->value = $fmt->pFloat($row['value']);
        return $pricing;
    }

    function formatRow($pricing, $fmt)
    {
        $row = array(
                     'id' => $fmt->fInt($pricing->id),
                     'name' => $fmt->fString($pricing->name),
                     'type' => $fmt->fString($pricing->type),
                     'value' => $fmt->fFloat($pricing->value)
                     );
        return $row;
    }
    
    function getList($offset, $limit)
    {
        $dbh = mm_getDatabase();
        $fmt = $dbh->getFormatter();
        $sql = sprintf("SELECT id, name, type, value FROM" .
                " mm_pricing" .
                " LIMIT %d, %d", $offset, $limit);
        $res = $dbh->query($sql);
        $pricings = array();
        while ($row = $res->fetchAssoc()) {
            $pricings[] = $this->parseRow($row);
        }
        return $pricings;
    }
    
    function getPricingsForProduct($product)
    {
        if (!$product || !$product->id) return array();
        return $this->getPricingsForProductId($product->id);
    }
    
    function getPricingsForProductId($product_id)
    {
        $dbh = mm_getDatabase();
        $fmt = $dbh->getFormatter();
        $sql = "SELECT p.id, p.name, p.type, p.value FROM" .
                " mm_pricing p" .
                ", mm_pricing_category pc, mm_product_category prdcat" .
                " WHERE prdcat.product_id=" . $fmt->fInt($product_id) .
                " AND pc.category_id = prdcat.category_id" .
                " AND p.id = pc.pricing_id";
        $res = $dbh->query($sql);
        $list = array();
        while ($row = $res->fetchAssoc()) {
            $list[] = $this->parseRow($row);
        }
        return $list;
    }
    
    function makeProductIdToPricingLookup($product_ids)
    {
        if (!$product_ids) return array();
        
        $dbh = mm_getDatabase();
        $fmt = $dbh->getFormatter();
        $product_id_list = implode(', ', array_map('intval', $product_ids));
                
        $sql = "SELECT prdcat.product_id, pc.category_id," .
                "   p.id, p.name, p.type, p.value" .
                " FROM" .
                "   mm_pricing p, mm_pricing_category pc, " .
                "   mm_product_category prdcat" .
                " WHERE " .
                "   prdcat.product_id IN (" . $product_id_list . ")" .
                "   AND pc.category_id = prdcat.category_id" .
                "   AND p.id = pc.pricing_id" .
                " ORDER BY prdcat.product_id, pc.category_id, p.id";
        $res = $dbh->query($sql);
        $lookup = array();
        foreach ($product_ids as $product_id) {
            $lookup[$product_id] = array();
        }
        while ($row = $res->fetchAssoc()) {
            $pricing = $this->parseRow($row);
            $product_id = $row['product_id'];
            $add = true;
            // Remove duplicate pricing
            if (count($lookup[$product_id]) > 0) {
                foreach ($lookup[$product_id] as $p) {
                    if ($p->id == $pricing->id) {
                        $add = false;
                        break;
                    }
                }
            }
            if ($add) $lookup[$product_id][] = $pricing;
        }
        return $lookup;
    }

    // INSERT
    
    function deleteAllPricingCategories()
    {
        mm_getDatabase()->query('delete from mm_pricing_category');
    }
    
    function getPricingCategoryCount()
    {
        return (int) mm_getDatabase()->getOne("select count(id) " .
                "from mm_pricing_category");
    }
    
    function getCategories($pricing)
    {
        return $this->getCategoriesForId($pricing->id);
    }

    /**
     * Get a list of categories for a given pricing ID.
     */
    function getCategoriesForId($id)
    {
        $dao = new category_CategoryDAO;
        return $categories = $dao->find(array('select' => "c.*", 'from' => "mm_category AS c, mm_pricing_category AS pc", 'where' => array("c.id = pc.category_id AND pc.pricing_id=?", $id)));
    }
    
    function addCategoryIdsToPricing($pricing, $category_ids)
    {
        if (!$category_ids) return;
        
        $dbh = mm_getDatabase();
        $fmt = $dbh->getFormatter();
        $sql = "INSERT INTO mm_pricing_category (" .
                "pricing_id," .
                "category_id" .
                ") VALUES ";
        $i = 0;
        foreach ($category_ids as $category_id) {
            if ($i > 0) $sql .= ',';
            $sql .= '(' .
                $fmt->fInt($pricing->id) . ',' .
                $fmt->fInt($category_id) . ')';
            $i++;
        }
        $dbh->execute($sql);
    }
    
    function addCategoriesToPricing($pricing, $categories)
    {
        $category_ids = array();
        foreach ($categories as $category) {
            $category_ids[] = $category->id;
        }
        return $this->addCategoryIdsToPricing($pricing, $category_ids);
    }
}
