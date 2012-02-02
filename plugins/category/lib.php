<?php

/**
 * Query the database for all categories, sorted by 'lft'.
 */
function cat_getAllByLft() {
    $dbh = mm_getDatabase();
    $sql = "select * from mm_category order by lft";
    $res = $dbh->query($sql);
    $cats = array();
    while ($cat = $res->fetchAssoc()) {
        $cat['level'] = 0;
        $cat['children'] = array();
        $cats[] = $cat;
    }
    return $cats;
}

/**
 * Convert a flat array of categories into a hierarchical data graph based on parent-child relationship.
 */
function cat_collect(&$cats) {
    $children = array();
    $parentsById = array();
    foreach ($cats as &$cat) {
        if ($cat['parent_id'] == 0) {
            $cat['level'] = 0;
            $parentsById[$cat['id']] =& $cat;
        }
        else {
            $children[] =& $cat;
        }
    }
    $results = cat_collect2($parentsById, $children, 0);
    
    return $parentsById;
}

/**
 * Helper function to cat_collect().
 */
function cat_collect2(&$parentsById, &$children, $level) {
    if (!$children) {
        return;
    }
    $newParentsById = array();
    $newChildren = array();
    foreach ($children as &$cat) {
        if (array_key_exists($cat['parent_id'], $parentsById)) {
            $cat['level'] = $level;
            $parentsById[$cat['parent_id']]['children'][] =& $cat;
            $newParentsById[$cat['id']] =& $cat;
        }
        else {
            $newChildren[] =& $cat;
        }
    }
    
    $result = cat_collect2($newParentsById, $newChildren, $level + 1);
    
    return array('parentsById' => &$newParentsById, 'children' => &$newChildren);
}

/**
 * Print from hierarchical data structure.
 */
function cat_printLevel($parentsById, $level=0, $showChildren=true) {
    if (!$parentsById) {
        return;
    }
    foreach ($parentsById as $cat) {
        //var_export($cat);
        echo str_repeat('  ', $level);
        echo "id={$cat['id']} p={$cat['parent_id']}, ({$cat['lft']}|{$cat['rgt']}) {$cat['name']}";
        echo "\n";
        if ($showChildren) {
            cat_printLevel($cat['children'], $level + 1);
        }
    }
}

/**
 * Fix the lft/rgt values for a hierarchical data structure.
 */
function cat_fix(&$cats, &$lft) {
    if (!$cats) {
        return;
    }
    if (!isset($lft)) {
        $lft = 0;
    }
    foreach ($cats as $key=>$cat) {
        $cats[$key]['lft'] = $lft++;
        if (empty($cats[$key]['children'])) {
            $cats[$key]['rgt'] = $lft++;
        }
        else {
            cat_fix($cats[$key]['children'], $lft);
            $cats[$key]['rgt'] = $lft++;
        }
    }
}

function cat_updateAll($cats, $debug=false)
{
    if (!$cats) return;
    $db = mm_getDatabase();
    echo "Got db\n";
    foreach ($cats as $key=>$cat) {
        if ($debug) {
            echo "query: 'UPDATE mm_category SET lft=?, rgt=? WHERE id=?'\n";
            echo "  values: ", var_export(array($cat['lft'], $cat['rgt'], $cat['id']), true), "\n";
        }
        $db->execute("UPDATE mm_category SET lft=?, rgt=? WHERE id=?", array($cat['lft'], $cat['rgt'], $cat['id']));
        if (!empty($cat['children'])) {
            cat_updateAll($cat['children']);
        }
    }
}
