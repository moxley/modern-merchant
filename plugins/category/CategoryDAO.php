<?php
/**
 * @package category
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * @package category
 * Category data access object.
 */
class category_CategoryDAO extends mvc_DataAccess
{
    static $default_category = null;
    static $cached_categories = array();
    static $cached_by_url_name = array();
    static $cached_children_by_id;
    public $regular_columns = array('parent_id', 'lft', 'rgt', 'name', 'url_name', 'description', 'comment', 'image_id', 'sortorder', 'keywords');
    public $primary = "id";
    
    function createCategoryTable()
    {
        $db = mm_getDatabase();
        
        $drop = "DROP TABLE IF EXISTS `mm_category`";
        $db->execute($drop);
        
        $create = "CREATE TABLE `mm_category` (
          id           int NOT NULL auto_increment,
          lft          int NOT NULL,
          rgt          int NOT NULL,
          parent_id    int NOT NULL default '0',
          name         varchar(40) default NULL,
          url_name     varchar(255),
          image_id     int,
          description  text,
          comment      text,
          sortorder    integer not null default '0',
          keywords varchar(255),
          PRIMARY KEY  (`id`),
          KEY `parent_id` (`parent_id`),
          KEY `sortorder` (`sortorder`),
          KEY `url_name` (`url_name`),
          KEY `lft` (`lft`),
          KEY `rgt` (`rgt`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8";
        $db->execute($create);
    }
        
    /**
     * Fetch a category for a given ID.
     *
     * @param integer $id
     * @return category_Category
     */
    function fetch($id, $options=array()) {
        if (is_numeric($id) && gv($options, 'use_cache')) {
            $this->cacheCategories();
            return gv(self::$cached_categories, $id);
        }
        else {
            return parent::fetch($id);
        }
    }
    
    function fetchForEdit($id)
    {
        $category = $this->fetch($id);
        
        /* Build a list of siblings */
        $db = mm_getDatabase();
        $category->siblings = array_map(array($this, 'parseRow'),
            $db->getAllAssoc("SELECT * FROM mm_category WHERE parent_id=? AND id != ? ORDER BY lft", array($category->parent_id, $category->id)));
        
        /* Set the place_before property */
        $category->place_before = null;
        foreach (array_reverse($category->siblings) as $sibling) {
            if ($category->lft < $sibling->lft) {
                $category->place_before = $sibling->id;
            } else {
                break;
            }
        }
        
        return $category;
    }
    
    function fetchByUrlName($url_name) {
        $this->cacheCategories();
        return self::$cached_by_url_name[$url_name];
    }
    
    function cacheCategories() {
        if (!self::$cached_categories) {
            $sql = "SELECT c.*, (count(r.id)-1) as depth FROM mm_category c, mm_category r WHERE c.lft BETWEEN r.lft AND r.rgt GROUP BY c.id";
            $all = $this->findBySql($sql, array('order' => 'c.lft, c.name'));
            foreach ($all as $c) {
                self::$cached_categories[$c->id] = $c;
                self::$cached_by_url_name[$c->url_name] = $c;
            }
        }
    }
    
    function showCategories()
    {
        echo $this->allToString();
    }
    
    function allToString()
    {
        $all = $this->find(array('order' => 'lft'));
        $level = 0;
        $i = 0;
        $size = count($all);
        $out = '';
        while ($i < count($all)) {
            $i = $this->itemToString($all, $i, $level, $size, $out);
        }
        return $out;
    }

    function itemToString($all, $i, $level, $size, &$out)
    {
        $start_i = $i;
        while ($i < $start_i + $size && $i < count($all)) {
            $c = $all[$i];
            $out .= sprintf("%sid=%02d p=%02d   [%02d-%02d] \"%s\"\n",
                    str_repeat('  ', $level),
                    $c->id,
                    $c->parent_id,
                    $c->lft,
                    $c->rgt,
                    $c->name);
            $i++;
            $c_size = ($c->rgt - $c->lft + 1) / 2 - 1;
            if ($c_size > 0) {
                $i = $this->itemToString($all, $i, $level + 1, $c_size, $out);
            }
        }
        return $i;
    }

    function showChildren()
    {
        $parent_id = 4;
        echo "parent_id=$parent_id\n";
        $cats = $this->find(array('where' => array('parent_id=?', 4), 'order' => 'lft'));
        foreach ($cats as $c) {
            printf("id=%02d [%02d-%02d]\n",
                   $c->id,
                   $c->lft,
                   $c->rgt);
        }
    }

    function getPlacementList()
    {
        $all = $this->find(array('order' => 'lft'));
        $level = 0;
        $i = 0;
        $size = count($all);
        $list = array();
        while ($i < count($all)) {
            list($i, $list) = $this->getPlacementItem($all, $i, $level, $size, $list);
        }
        return $list;
    }

    function getPlacementItem($all, $i, $level, $size, $list)
    {
        $start_i = $i;
        while ($i < $start_i + $size) {
            $c = $all[$i];

            $list[] = array('level'     => $level,
                            'id'        => $c->id,
                            'name'      => $c->name);

            $i++;
            $c_size = ($c->rgt - $c->lft + 1) / 2 - 1;
            if ($c_size > 0) {
                list($i, $list) = $this->getPlacementItem($all, $i, $level + 1, $c_size, $list);
            }
        }
        return array($i, $list);
    }

    function showPlacementList()
    {
        $list = $this->getPlacementList();
        foreach ($list as $c) {
            echo str_repeat('  ', $c['level']) . $c['id'] . ': ' . $c['name'] . "\n";
        }
    }

    function test()
    {
        $this->restoreCategories();
        //$this->testBefore(5, 9);
        $this->testBefore(5, 8);
    }

    function testBefore($id, $before)
    {
        $c = $this->fetch($id);
        $c->place_before = $before;
        echo "Moving category $id to before {$c->place_before}\n";
        echo "Graph before:\n";
        $this->showCategories();
        $this->moveAmongSiblings($c);
        echo "Graph after:\n";
        $this->showCategories();
        echo "\n";
        $this->restoreCategories();
    }

    /**
     * Open up a hole in the lft-rgt sequence for a new category.
     */
    function moveAmongSiblings($subject)
    {
        if (!isset($subject->place_before)) {
            return;
        }
        $place_before = null;
        if ($subject->place_before) {
            $place_before = $this->fetch($subject->place_before);
        }

        if (!$subject->parent_id && !$place_before) {
            // Error
            trigger_error("Category has no parent and no place_before", E_USER_WARNING);
            return false;
        }

        if ($subject->lft !== null && $subject->rgt !== null) {
            $subject_width = $subject->rgt - $subject->lft + 1;
        }
        else {
            $subject_width = 2;
        }
        $parent = $subject->parent;
        $is_new = !$subject->lft;
        $db = mm_getDatabase();

        if (!$place_before) {
            /*
             * ** Append to the end **
             */
            
            // If subject is not new:
            //   Move subject out of temporary spot into permanent spot
            if ($subject->parent_id) {
                // Shift categories whose lft or rgt is greater or equal to parent->rgt
                $db->execute("UPDATE mm_category c SET c.lft = c.lft + ? WHERE c.lft >= ?",
                             array($subject_width, $subject->parent->rgt));
                $db->execute("UPDATE mm_category c SET c.rgt = c.rgt + ? WHERE c.rgt >= ?",
                             array($subject_width, $subject->parent->rgt));
                $subject->lft = $subject->parent->rgt;
                $subject->rgt = $subject->lft + $subject_width - 1;
                $subject->parent->rgt = $subject->rgt + 1;
            }
            else {
                $count = $db->getOne("SELECT COUNT(id) FROM mm_category");
                $subject->rgt = $count - 1;
                $subject->lft = $subject->rgt - $subject_width + 1;
            }
        }
        else {
            if ($place_before->parent_id == $subject->parent_id) {
                $db->execute("UPDATE mm_category c SET c.lft = c.lft + ? WHERE c.lft >= ?",
                             array($subject_width, $place_before->lft));
                $db->execute("UPDATE mm_category c SET c.rgt = c.rgt + ? WHERE c.rgt >= ?",
                             array($subject_width, $place_before->rgt));
                $subject->lft = $place_before->lft;
                $subject->rgt = $subject->lft + $subject_width - 1;
            }
            else if ($place_before->parent_id != $subject->parent_id) {
            
                // If subject is not new:
                if ($is_new) {
                    throw new Exception("Not implemented");
                }
                else {
                    if ($subject->lft > $place_before->lft) {

                        $offset = $subject->lft - $place_before->lft;

                        // Move the subject to the new location, times -1
                        $db->execute("UPDATE mm_category c SET c.lft = (c.lft - ?) * -1, c.rgt = (c.rgt - ?) * -1 WHERE c.lft >= ? AND c.rgt <= ?",
                                     array($offset, $offset, $subject->lft, $subject->rgt));
                    
                        // Contract to fill up the subject's old space
                        //$db->execute("UPDATE mm_category SET lft = lft - ?, rgt = rgt - ? WHERE lft >= ?",
                        //    array($subject_width, $subject_width, $subject->rgt + 1));
                        $db->execute("UPDATE mm_category SET lft = lft - if(lft >= ?, ?, 0), rgt = rgt - if(rgt >= ?, ?, 0)",
                            array($subject->rgt + 1, $subject_width, $subject->rgt + 1, $subject_width));
                    
                        // Make room for the subject's new place
                        $db->execute("UPDATE mm_category SET lft = lft + if(lft >= ?, ?, 0), rgt = rgt + if(rgt >= ?, ?, 0)",
                            array($place_before->lft, $subject_width, $place_before->lft, $subject_width));
                    
                        // Move the subject out of temporary space
                        $db->execute("UPDATE mm_category SET lft = lft * -1, rgt = rgt * -1 WHERE lft <= ? AND rgt >= ?",
                            array(-$place_before->lft, -($place_before->lft + $subject_width - 1)));
                    
                        // Make room for the subject
                    
                        $subject->lft -= $offset;
                        $subject->rgt -= $offset;
                    }
                    else if ($subject->lft < $place_before->lft) {
                        $offset = $place_before->lft - $subject->lft;
                    
                        // Move the subject to the new location, times -1
                        $db->execute("UPDATE mm_category SET lft = -1 * (lft + ?), rgt = -1 * (rgt + ?) WHERE lft >= ? AND rgt <= ?",
                            array($offset - $subject_width, $offset - $subject_width, $subject->lft, $subject->rgt));
                    
                        // Contract the space that previously held $subject
                        $db->execute("UPDATE mm_category SET lft = lft - if(lft >= ?, ?, 0), rgt = rgt - if(rgt >= ?, ?, 0)",
                            array($subject->rgt + 1, $subject_width, $subject->rgt + 1, $subject_width));
                    
                        // Make room for subject
                        //$db->execute("UPDATE mm_category SET lft = lft + ?, rgt = rgt + ? WHERE lft >= ? AND rgt <= ?",
                        //    array($subject_width, $subject_width, $place_before->lft - $subject_width, $place_before->rgt - $subject_width));
                        $db->execute("UPDATE mm_category set lft = lft + if(lft >= ?, ?, 0), rgt = rgt + if(rgt >= ?, ?, 0)",
                            array($place_before->lft - $subject_width, $subject_width, $place_before->lft - $subject_width, $subject_width));
                    
                        // Move subject to permanent location
                        $db->execute("UPDATE mm_category SET lft = -1 * lft, rgt = -1 * rgt WHERE lft <= ? AND rgt >= ?",
                            array(-($place_before->lft - $subject_width), -($place_before->lft - 1)));
                    
                        $subject->lft = $place_before->lft - $subject_width;
                        $subject->rgt = $place_before->lft - 1;
                    }
                    else {
                        throw new Exception("Should not be here.");
                    }
                }
            }
            else if ($place_before->lft == $subject->lft + $subject_width) {
                // No update needed: Same place as before
                return true;
            }
        }
    }

    function backUpCategories() {
        $all = $this->find(array('order' => 'lft'));
        $data = array();
        foreach ($all as $c) {
            $data[] = array(
                'id' => $c->id,
                'lft' => $c->lft,
                'rgt' => $c->rgt,
                'parent_id' => $c->parent_id);
        }
        $file = MM_LIB . '/private/categories.data';
        file_put_contents($file, serialize($data));
        echo "file: ", file_get_contents($file), "\n";
        var_export($data);
    }

    function restoreCategories()
    {
        $file = MM_LIB . '/private/categories.data';
        $data = unserialize(file_get_contents($file));
        $db = mm_getDatabase();
        foreach ($data as $d) {
            $db->execute("UPDATE mm_category SET parent_id=?, lft=?, rgt=? WHERE id=?",
                         array($d['parent_id'],
                               $d['lft'],
                               $d['rgt'],
                               $d['id']));
        }
    }
    
    function removeSpace($category) {
        $db = mm_getDatabase();
        $width = $category->rgt - $category->lft + 1;
        $right = $category->rgt;
        $db->execute("UPDATE mm_category SET rgt = rgt - ? WHERE rgt > ?", array($width, $right));
        $db->execute("UPDATE mm_category SET lft = lft - ? WHERE lft > ?", array($width, $right));
    }
    
    /**
     * Add a category to the database.
     *
     * @param category_Category $category
     */
    function add($category)
    {
        $parent = null;
        if ($category->parent_id && !$category->parent) {
            throw new mm_NoMatchException("parent category id=" . $category->parent_id);
        }
        $this->moveAmongSiblings($category);
        
        if (!parent::add($category)) {
            return false;
        }
        else {
            // Set default category
            if (isset($category->default)) {
                if ($category->default) {
                    mm_setSetting('catalog.default_category', $category->id);
                }
                else {
                    mm_setSetting('catalog.default_category', null);
                }
            }
            return true;
        }
    }

    function deleteAll()
    {
        mm_getDatabase()->query("delete from mm_category");
        mm_getDatabase()->query("delete from mm_product_category");
        mm_getDatabase()->query("delete from mm_pricing_category");
    }
    
    function delete($category) {
        $dependants = $this->getFlattenedHierarchy($category->id);
        foreach ($dependants as $d) $this->deleteProductCategories($d);
        $this->deleteProductCategories($category);
        $this->removeSpace($category);
        return parent::delete($category);
    }
    
    function deleteProductCategories($category) {
        mm_getDatabase()->execute("DELETE FROM mm_product_category WHERE category_id=?", $category->id);
    }
        
    function getCount()
    {
        return mm_getDatabase()->getOne("select count(*) from mm_product_category");
    }
    
    /**
     * Persist changes made to a saved category.
     *
     * @param category_Category $category
     * @param mvc_FileUpload $upload
     */
    function update($category) {
        /* Set new sort order */
        $this->moveAmongSiblings($category);

        // Set default category
        if (isset($category->default)) {
            if ($category->default) {
                mm_setSetting('catalog.default_category', $category->id);
            }
            else {
                mm_setSetting('catalog.default_category', null);
            }
        }
        
        return parent::update($category);
    }
    
    /**
     * Get child categories belonging to the category with the given ID.
     *
     * @param $parent_id int
     * @return array
     */
    function getChildren($parent_id=0) {
        if (!isset(self::$cached_children_by_id[$parent_id])) {
            self::$cached_children_by_id = array();
            $children = array();
            $flat_list = $this->getFlattenedHierarchy($parent_id);
            $stack = array();
            foreach ($flat_list as $c) {
                while (!empty($stack)) {
                    $top = $stack[count($stack)-1];
                    if ($c->parent_id == $top->id) {
                        $c->setParent($top);
                        $top->add($c);
                        $stack[] = $c;
                        break;
                    }
                    else {
                        array_pop($stack);
                    }
                }
                if (empty($stack)) {
                    $stack[] = $c;
                    $children[] = $c;
                }
            }
            self::$cached_children_by_id[$parent_id] = $children;
        }
        return self::$cached_children_by_id[$parent_id];
    }
    
    /**
     * Get flattened hierarchy of categories. Useful for pull-downs.
     *
     * @param $parent_id int
     * @return array
     */
    function getFlattenedHierarchy($parent_id=0) {
        $this->cacheCategories();
        $parent = null;
        $list = array();
        $str = '';
        foreach (self::$cached_categories as $id=>$c) {
            if ($parent_id == 0) {
                $list[] = $c;
            }
            else if (!$parent) {
                if ($c->id == $parent_id) $parent = $c;
            }
            else {
                if ($c->rgt < $parent->rgt) {
                    $list[] = $c;
                }
                else {
                    break;
                }
            }
        }
        return $list;
    }
    
    function getListForProductId($product_id)
    {
        $sql = "select c.* from mm_category c, " .
            "mm_product_category pc " .
            "where pc.product_id=" . intval($product_id) .
            " and c.id=pc.category_id " .
            "order by c.sortorder";
        $dbh = mm_getDatabase();
        $res = $dbh->query($sql);
        $categories = array();
        while ($row = $res->fetchAssoc()) {
            $categories[] = $this->parseRow($row);
        }
        return $categories;
    }
    
    function parseRow($row, $options=array())
    {
        $category = new category_Category;
        $category->id           = (int) $row['id'];
        $category->name           = $row['name'];
        $category->_parent_id  = $row['parent_id'] === null ? null : (int) $row['parent_id'];
        $category->description = $row['description'];
        $category->comment       = $row['comment'];
        $category->_image_id   = $row['image_id'];
        $category->sortorder   = (int) $row['sortorder'];
        $category->keywords       = $row['keywords'];
        $category->lft     = (int) $row['lft'];
        $category->rgt    = (int) $row['rgt'];
        $category->depth       = (int) gv($row, 'depth', 0);
        $category->default       = mm_getSetting('catalog.default_category') == $category->id;
        $category->_url_name   = $row['url_name'];
        return $category;
    }
    
    function fixHierarchy() {
        $this->fixHierarchyChildren(null);
    }
    
    function fixHierarchyChildren($parent) {
        if (!$parent) {
            $parent_id = 0;
        }
        else {
            $parent_id = $parent->id;
        }
        $children = $this->find(array('where' => array('parent_id=?', $parent_id), 'order' => 'lft, id'));
        if ($children) {
            foreach ($children as $i=>$child) {
                if ($i == 0) {
                    $child->lft = $parent ? $parent->lft + 1 : 0;
                }
                else {
                    $child->lft = $previous_right + 1;
                }
                $this->fixHierarchyChildren($child);
                $previous_right = $child->rgt;
            }
            $last_child = $children[count($children) - 1];
            if ($parent) $parent->rgt = $last_child->rgt + 1;
        }
        else {
            if ($parent) $parent->rgt = $parent->lft + 1;
        }
        if ($parent) $parent->save();
    }
}
