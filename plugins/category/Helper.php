<?php
/**
 * @package category
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * @package category
 */
class category_Helper
{
    public $controller;
    
    function __construct($controller)
    {
        $this->controller = $controller;
    }

    function writeCategories($params)
    {
        $pc = new category_CategoryDAO();
        $list = gv($params, 'list', array());
        if (!$list) {
            $parent_id = gv($params, 'parent_id');
            if (!$parent_id) $parent_id = mm_getSetting('catalog.root_category');
            $list = $pc->getChildren($parent_id);
        }
        $script = mm_getConfigValue('urls.catalog.product_list');
        
        // Call getUrl() to rewrite the URL
        $schema = gv($params, 'schema');
        if ($schema) {
            $geturl_params = array('url' => $script, 'schema' => 'http');
            $params['script'] = $this->controller->getUrl($geturl_params);
        } 
        
        $list_type = gv($params, 'list_type', 'ul');
        if ($list_type == 'br') {
            $this->writeCategoriesAsBR($list, $params);
        }
        elseif ($list_type == 'ul') {
            $this->writeCategoriesAsUL($list, 0, $params);
        }
    }

    function writeCategoriesAsUL($list, $level, $options)
    {
        if (!is_array($options)) {
            $options = array('script' => $options);
        }
        echo "<ul class=\"tier$level\">\n";
        foreach ($list as $category) {
            echo "<li>";
            if ($callback = gv($options, 'href_callback')) {
                $href = call_user_func_array($callback, array($level, $category));
            }
            else {
                $id = intval($category->id);
                $href = appendQueryToUrl(gv($options, 'script'), "category_id=$id");
            }
            echo "<a href=\"" . h($href) . "\">";
            echo h($category->name) . "</a>";
            if ($children = $category->getChildren()) {
                $this->writeCategoriesAsUL($children, $level+1, $options);
            }
            echo "</li>\n";
        }
        echo "</ul>\n";
    }
    
    function writeCategoriesAsBR(&$list, $script)
    {
        foreach ($list as $category) {
            $id = intval($category->id);
            $href = appendQueryToUrl($script, "category_id=$id");
            echo "<a href=\"" . h($href) . "\">";
            echo h($category->name) . "</a>";
            echo "<br />";
        }
    }
}
