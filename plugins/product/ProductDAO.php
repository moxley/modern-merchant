<?php
/**
 * @package product
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class product_ProductDAO extends mvc_DataAccess
{
    public $regular_columns = array('created_on', 'modify_date', 'modify_user', 'sku', 'sortorder', 'name', 'active', 'description', 'comment', 'price', 'count', 'weight', 'available_on', 'keywords');
    
    function deleteAll()
    {
        mm_getDatabase()->query('delete from mm_product');
        mm_getDatabase()->query('delete from mm_product_category');
    }
    
    function getSelectColumns() {
        $columns = array();
        foreach ($this->regular_columns as $column) {
            if (is_array($column)) {
                $columns[] = $column[0];
            }
            else {
                $columns[] = $column;
            }
        }
        return 'p.id, p.' . implode(', p.', $columns);
    }
    
    function deleteByIds($id_array) {
        $dbh = mm_getDatabase();
        $id_string = implode(',', array_map('intval', $id_array));
        $sql = "delete from mm_product where id in ($id_string)";
        $dbh->execute($sql);
        $mdao = new media_MediaDAO;
        $media_list = $mdao->getListForProductIds($id_array);
        foreach ($media_list as $media) {
            $mdao->delete($media);
        }
        return TRUE;
    }
    
    function getCount()
    {
        return (int) mm_getDatabase()->getOne('select count(id) from mm_product');
    }
    
    function add($product)
    {
        $db = mm_getDatabase();
        $fmt = $db->getFormatter();
        
        // Get highest sort order
        $sql = "select max(sortorder) from mm_product";
        $highest = $db->getOne($sql);
        $product->sortorder = $highest + 1;
        
        $product->modify_date = mm_time();
        if (!$product->sku) {
            $product->sku = uniqid('');
        }
        $sql = sprintf("INSERT INTO mm_product (" .
                "created_on," .
                "modify_date," .
                "modify_user," .
                "sku," .
                "sortorder," .
                "name," .
                "active," .
                "description," .
                "comment," .
                "price," .
                "count," .
                "weight," .
                "available_on," .
                "keywords" .
                ") values (" .
                "%s, %s, %s, " .
                "%s, %s, %s, " .
                "%s, %s, %s, " .
                "%s, %s, %s, " .
                "%s, %s)",
                $fmt->fDate($product->created_on),
                $fmt->fDate($product->modify_date),
                $fmt->fString($product->modify_username),
                $fmt->fString($product->sku),
                $fmt->fInt($product->sortorder),
                $fmt->fString($product->name),
                $fmt->fBool01($product->active),
                $fmt->fString($product->description),
                $fmt->fString($product->comment),
                $fmt->fMoney($product->price),
                $fmt->fInt($product->count),
                $fmt->fFloat($product->weight),
                $fmt->fDate($product->available_on),
                $fmt->fString($product->keywords));
        //echo "sql: $sql\n";
        $db->execute($sql);
        $product->id = $db->lastInsertId();
        if ($product->sku_same_as_id) {
            $product->sku = $product->id;
            $db->execute("UPDATE mm_product SET sku=? WHERE id=?", array($product->sku, $product->id));
        }
        return $product;
    }
    
    function update($product)
    {
        $dbh = mm_getDatabase();
        $fmt = $dbh->getFormatter();
        $product->modify_date = mm_time();
        $sql = "UPDATE mm_product SET " .
                "modify_date=?, modify_user=?, sku=?, sortorder=?, " .
                "name=?, active=?, description=?, comment=?, " .
                "price=?, count=?, weight=?, available_on=?, keywords=? " .
                "WHERE id=?";
        $values = array(
                array($product->modify_date, 'type' => 'datetime'),
                $product->modify_username,
                $product->sku,
                $product->sortorder,
                $product->name,
                $product->active ? 1 : 0,
                $product->description,
                $product->comment,
                $product->price,
                $product->count,
                $product->weight,
                array($product->available_on, 'type' => 'datetime'),
                $product->keywords,
                $product->id);
        $dbh->execute($sql, $values);
        return $product;
    }
    
    /**
     * @param Array $products  A list of products, each an associative array
     */
    function updateMultiple($products, $user)
    {
        $delete_products = array();
        foreach ($products as $id=>$product)
        {
            if (!$id) continue;
            if (gv($product, 'delete')) $delete_products[] = intval($id);
            $product['id'] = $id;
            $products[$id] = $product;
        }
        
        $modify_date = time();
        $username = $user->username;
        
        $updated = 0;
        $db = mm_getDatabase();
        foreach ($products as $id=>$product)
        {
            $query = "
                    UPDATE mm_product SET
                    modify_date=".i($modify_date).",
                    modify_user=".dq($username).",
                    count=".(($product['count'] != '') ? intval($product['count']) : "NULL").",
                    sortorder=".(($product['sortorder']) ? intval($product['sortorder']) : "NULL").",
                    name=".dq(trim($product['name'])).",
                    price=".(sprintf("%0.2f", $product['price'])).",
                    active=".(gv($product, 'active') ? '1' : '0')."
                    WHERE id=".i($id)."
                ";
            $db->execute($query);
            $updated++;
        }
        
        $deleted = 0;
        if ($delete_products) {
            $deleted = $this->deleteByIds($delete_products);
        }
        
        return array($updated, $deleted);
    }
    
    function getListForCategory($category, $offset, $limit, $options=array())
    {
        return $this->getListForCategoryId($category->id, $offset, $limit, $options);
    }
    
    function parseOrder($order)
    {
        $parts = preg_split('/\s*,\s/', $order);
        foreach ($parts as $k=>$part) {
            if (strpos($part, '.') === false) {
                $parts[$k] = 'p.' . $part;
            }
        }
        $order = implode(', ', $parts);
        return $order;
    }

    /**
     * Get a list of <tt>product_Product</tt> objects by category_id.
     * 
     * @return array
     */
    function getListForCategoryId($category_id, $offset, $limit, $options=array())
    {
        $dbh = mm_getDatabase();
        
        $from = " from mm_product p, mm_product_category pc " .
                " where pc.category_id=" . intval($category_id) .
                " and p.id=pc.product_id ";
        $count_query = "select count(*) $from";
        $count = $dbh->getOne($count_query);
        if ($count == 0) return array(array(), $count);
        
        $order = array_delete_at($options, 'order');
        if (!$order) {
            $order = "pc.sortorder, p.sortorder, p.name";
        }
        else {
            $order = $this->parseOrder($order);
        }
        
        $query = "select " . $this->getSelectColumns() .
                " $from " .
                " order by $order " .
                " limit " . intval($offset) . "," . intval($limit);
        
        $products = $this->getListForQuery($query);

        return array($products, $count);
    }

    /**
     * Get a list of <tt>product_Product</tt> objects that don't belong to a category.
     * 
     * @return array
     */
    function getListForNoCategory($offset, $limit, $options=array())
    {
        $dbh = mm_getDatabase();
        
        $from = " FROM mm_product p " .
                " LEFT JOIN mm_product_category pc on pc.product_id=p.id" .
                " WHERE pc.category_id is NULL ";
        $count_query = "select count(*) $from";
        $count = $dbh->getOne($count_query);
        if ($count == 0) return array(array(), $count);
        
        $order = array_delete_at($options, 'order');
        if ($order) {
            $order = $this->parseOrder($order);
        }
        else {
            $order = "p.sortorder, p.name";
        }
        
        $query = "select " . $this->getSelectColumns() .
                " $from " .
                " order by $order " .
                " limit " . intval($offset) . "," . intval($limit);
        
        $products = $this->getListForQuery($query);

        return array($products, $count);
    }
    
    function getListForQuery($query)
    {
        $dbh = mm_getDatabase();
        $rs = $dbh->query($query);
        return $this->getListForResultSet($rs);
    }
    
    function getListForResultSet($rs) {
        $products = array();
        while ($row = $rs->fetchAssoc()) {
            $products[] = $this->parseRow($row);
        }
        return $products;
    }
    
    function findBySearch($q, $offset, $limit, $options=array()) {
        $db = mm_getDatabase();
        
        $likes = array();
        
        $likes[] = 'product.id = ?';
        $params[] = '%' . $q . '%';

        $likes[] = 'product.name LIKE ?';
        $params[] = '%' . $q . '%';
        
        $likes[] = "product.keywords LIKE ?";
        $params[] = '%' . $q . '%';

        $likes[] = "product.sku LIKE ?";
        $params[] = $q;
        
        $where = "(" . implode(' OR ', $likes) . ")";
        if ($conditions = array_delete_at($options, 'where')) {
            $where .= " AND (" . $conditions . ")";
        }
        $joins = '';
        if ($category_id = array_delete_at($options, 'category_id')) {
            $joins = '
                INNER JOIN mm_product_category pc ON pc.product_id = product.id
                INNER JOIN mm_category c ON c.id = pc.category_id
            ';
            $where .= ' AND pc.category_id = ?';
            $params[] = $category_id;
        }
        $sql = "SELECT COUNT(DISTINCT product.id) FROM mm_product product $joins WHERE $where";
        $count = $db->getOne($sql, $params);
        $sql = "SELECT DISTINCT product.id, product.* FROM mm_product product $joins WHERE $where LIMIT ?,?";
        $params[] = intval($offset);
        $params[] = intval($limit);
        $rs = $db->query($sql, $params);
        return array($this->getListForResultSet($rs), $count);
    }
    
    function attachMediaToProducts($products)
    {
        if (!$products) return;
        $mdao = new media_MediaDAO;
        if ($products) {
            $ids = array();
            $indexed_products = array();
            foreach ($products as $product) {
                $ids[] = $product->id;
                $product->_images = array();
                $indexed_products[$product->id] = $product;
            }
            $media_array = $mdao->getListForProductIds($ids);
            $product_images = array();
            foreach ($media_array as $media) {
                $product = $indexed_products[$media->owner_id];
                $product->_images[] = $media;
            }
        }
    }
    
    function attachPricingToProducts($products)
    {
        if (!$products) return;
        $pdao = new pricing_PricingDAO;
        $product_ids = array_map(create_function('$p', 'return $p->id;'), $products);
        $lookup = $pdao->makeProductIdToPricingLookup($product_ids);
        foreach ($products as $product) {
            $product->pricings = array();
            if (isset($lookup[$product->id])) {
                $product->pricings = $lookup[$product->id];
            }
        }
    }
    
    //function fetch($id, $options=array())
    //{
    //    $dbh = mm_getDatabase();
    //    
    //    $where = "id=" . intval($id);
    //    if ($conditions = array_delete_at($options, 'where')) {
    //        $where .= " AND " . $conditions;
    //    }
    //    $query = "SELECT " . $this->getSelectColumns() . " FROM mm_product p WHERE " . $where;
    //    $row = $dbh->getOneAssoc($query);
    //    if (!$row) return null;
    //    $product = $this->parseRow($row);
    //    return $product;
    //}
    
    function fetchBySku($sku, $options=array())
    {
        $dbh = mm_getDatabase();
        $fmt = $dbh->getFormatter();
        $where = "sku=?";
        if ($conditions = array_delete_at($options, 'where')) {
            $where .= " AND " . $conditions;
        }
        $query = "SELECT " . $this->getSelectColumns() . " FROM mm_product p WHERE $where";
        $row = $dbh->getOneAssoc($query, array($sku));
        if (!$row) return null;
        $product = $this->parseRow($row);
        return $product;
    }
    
    function parseRow($row, $options=array())
    {
        $fmt = mm_getDatabase()->getFormatter();
        $product = new product_Product;
        $product->id = $fmt->pInt($row['id']);
        $product->created_on = $fmt->pDate($row['created_on']);
        $product->modify_date = $fmt->pDate($row['modify_date']);
        $product->modify_username = $row['modify_user'];
        $product->sku = $row['sku'];
        $product->sortorder = $fmt->pInt($row['sortorder']);
        $product->name = $row['name'];
        $product->active = $fmt->pBool01($row['active']);
        $product->description = $row['description'];
        $product->comment = $row['comment'];
        $product->price = $fmt->pMoney($row['price']);
        $product->count = $fmt->pInt($row['count']);
        $product->weight = sprintf('%0.3f', $row['weight']);
        $product->available_on = $fmt->pDate($row['available_on']);
        $product->keywords = $row['keywords'];
        return $product;
    }
    
    function makeSkuLookup($skus)
    {
        $dbh = mm_getDatabase();
        $query = "SELECT " . $this->getSelectColumns() . " FROM mm_product p WHERE sku in (" .
                implode(',', array_map('dq', $skus)) . ")";
        $res = $dbh->query($query);
        $lookup = array();
        $products = array();
        while ($row = $res->fetchAssoc()) {
            $product = $this->parseRow($row);
            $products[] = $product;
            $lookup[$product->sku] = $product;
        }
        return $lookup;
    }
    
    function addProductToCategories($product, $categories)
    {
        $dbh = mm_getDatabase();
        $sql = "INSERT INTO mm_product_category (" .
                "product_id" .
                ",category_id" .
                ") values ";
        $i = 0;
        foreach ($categories as $category)
        {
            if ($i > 0) $sql .= ', ';
            $sql .= sprintf("(%d, %d)",
                $product->id, $category->id);
        }
        $dbh->execute($sql);
    }
    
    function getDescendantProducts($category_id, $offset, $limit, $options=array())
    {
        $depth = 5;
        
        // Get a list of all categories to fetch products from
        $category_ids = array($category_id);
        $last_set = $category_ids;
        $cat_dao = new category_CategoryDAO;
        $dbh = mm_getDatabase();
        $fmt = $dbh->getFormatter();
        for($i=1; $i < $depth; $i++)
        {
            $query = "select id from mm_category where parent_id IN ("
                . $fmt->fIntList($last_set) . ")";
            $last_set = $dbh->getCol($query);
            if( !$last_set ) break;
            $category_ids = array_merge($category_ids, $last_set);
        }

        $from = " from mm_product_category pc, mm_product p" .
                " where pc.category_id IN (" . $fmt->fIntList($category_ids) . ")" .
                " and p.id=pc.product_id" .
                " and (p.count > 0 OR p.count is NULL) " .
                " and p.active != 0";
        if ($conditions = array_delete_at($options, 'where')) {
            $from .= " and (" . $conditions . ")";
        }

        $count = $dbh->getOne("select count(*) $from");
        
        // Run the product query
        $query = "select " . $this->getSelectColumns() . " $from " .
                "ORDER BY p.sortorder, p.available_on DESC " .
                "LIMIT $offset, $limit";

        $products = $this->getListForQuery($query);
        
        return array($products, $count);
    }
}
