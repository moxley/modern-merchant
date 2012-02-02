<?php
/**
 * @package category
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class catalog_ProductGridHandler extends catalog_GridHandler {

    public $varname;

    function __construct() {
        $this->varname = 'product';
    }
    
    function &getList($params, $engine) {
        $category_id = gv($params, 'category_id', 0);
        $dao = new product_ProductDAO();
        $items = $dao->getListByCategoryId($category_id);
        $as_array = array();
        foreach ($items as $i) {
            $as_array[] = objectToAssoc($i);
        }
        return $as_array;
    }

    function handle_open($tag_arg, &$smarty) {
    
        $args = $this->parseArguments($tag_arg);
        $category_id_smarty_expr = gv($args, 'category_id');
        $category_id_expr = '0';
        $items_expr = '';
        if ($category_id_smarty_expr != null) {
            $category_id_expr = $this->parseArgAsPHP('category_id', $category_id_smarty_expr);
            $items_expr = '$category_id = ' . $category_id_expr . ";\n"
                . '$product_dao = new product_ProductDAO;' . "\n"
                . '$products = $dao->getListByCategoryId($category_id);' . "\n"
                . '$this->assign("products", $products);' . "\n"
                . '';
            $tag_arg .= ' products=$products';
        }
        return $items_expr . parent::handle_open($tag_arg, $smarty);
    }
    
    function handle_close($tag_arg, &$smarty) {
        return "\n" .
            "    }\n" .
            "    print \"    </td>\\n\";\n" .
            "    \$i++;\n" .
            "  }\n" .
            "  print \"  </tr>\\n\";\n" .
            "}\n" .
            "print \"</table>\\n\";\n";
    }
}
