<?php
/**
 * @package category
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * Utility class for generating grids of products or categories
 */
class catalog_GridHandler {
    private $content;
    public $varname;
    public $default_rows = 3;
    public $default_columns = 3;
    
    /**
     * Convert tags to an array of name=>unparsed_value elements.
     */
    public function parseArguments($tag_guts) {
        $tag_args = explode(' ', $tag_guts);
        $args = array();
        foreach ($tag_args as $arg) {
            if (!$arg) continue;
            list($name, $value) = explode('=', $arg);
            $args[$name] = $value;
        }
        return $args;
    }
    
    /**
     * Convert tag argument string into a PHP expression.
     */
    public function parseArgAsPHP($name, $arg_string) {
        $expression = '';
        if ($arg_string{0} == '$') {
            $attrib = substr($arg_string, 1);
            $expression = "\$this->_tpl_vars['$attrib']";
        }
        else {
            $expression = "'" . addslashes($arg_string) . "'";
        }
        return $expression;
    }
    
    function handle_open($tag_arg, $smarty) {
        $args_expr = $this->parseArguments($tag_arg);
        
        $rows_expr = gv($args_expr, 'rows', $this->default_rows);
        $cols_expr = gv($args_expr, 'cols', $this->default_columns);
        $list_smarty = gv($args_expr, 'items');
        if ($list_smarty != null) {
            $items_expr = $this->parseArgAsPHP('items', $list_smarty);
        }
        else {
            $items_expr = gv($args_expr, 'items', 'array()');
        }
        $item_expr = "'" . gv($args_expr, 'item', ""). "'";
        
        //$args = 'array(' . $args . ')';
        return "\n\$items = $items_expr;\n" .
            "\$rows = $rows_expr;\n" .
            "\$cols = $cols_expr;\n" .
            "\$cells = \$rows * \$cols;\n" .
            "\$i = 0;\n" .
            'print "<table class=\\"grid\\">\\n";' . "\n" .
            "for(\$row=0; \$i < \$cells; \$row++) {\n" .
            "  print \"  <tr>\\n\";\n" .
            "  for(\$col=0; \$col < \$cols; \$col++) {\n" .
            "    \$item = array();\n" .
            "    if (\$i < count(\$items)) \$item = \$items[\$i];\n" .
            "    \$this->assign($item_expr, \$item);\n" .
            "    print \"    <td>\\n\";\n" .
            "    if (!\$item) echo \"&nbsp;\";\n" .
            "    else {\n";
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
