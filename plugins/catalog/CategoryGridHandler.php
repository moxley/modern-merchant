<?php
/**
 * @package category
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class catalog_CategoryGridHandler extends catalog_GridHandler {

    public $varname;
    
    function __construct() {
        $this->varname = 'category';
    }

    static function &getCategoryList($params, $engine) {
        $parent_id = gv($params, 'parent_id', 0);
        return self::getList($parent_id);
    }

    static function &getList($parent_id=0) {
        $dao = new category_CategoryDAO();
        return $dao->getChildren($parent_id);
    }
    
    function _handle_open($tag_arg, $smarty) {
    
        $tag_args = explode(' ', $tag_arg);
        $args = '';
        $i = 0;
        foreach ($tag_args as $arg) {
            if (!$arg) continue;
            list($name, $value) = explode('=', $arg);
            if ($i > 0) $args .= ', ';
            $args .= "'" . addslashes($name) . "' => '" . addslashes($value) . "'";
            $i++;
        }
        $args = 'array(' . $args . ')';
        
        return "\n\$items = catalog_CategoryGridHandler::getCategoryList($args, \$this);\n" .
            "\$rows = 3;\n" .
            "\$cols = 3;\n" .
            "\$cells = \$rows * \$cols;\n" .
            "\$i = 0;\n" .
            'print "<table class=\\"grid\\">\\n";' . "\n" .
            "for(\$row=0; \$i < \$cells; \$row++) {\n" .
            "  print \"  <tr>\\n\";\n" .
            "  for(\$col=0; \$col < \$cols; \$col++) {\n" .
            "    \$item = array();\n" .
            "    if (\$i < count(\$items)) \$item = \$items[\$i];\n" .
            "    \$this->assign('{$this->varname}', \$item);\n" .
            "    print \"    <td>\\n\";\n" .
            "    if (!\$item) echo \"&nbsp;\";\n" .
            "    else {\n";
    }
    
    function _handle_close($tag_arg, &$smarty) {
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
