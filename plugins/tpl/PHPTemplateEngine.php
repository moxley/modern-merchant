<?php
/**
 * @package tpl
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class tpl_PHPTemplateEngine
{
    public $controller;
    public $view;
    public $type = 'php';
    public $file_extension = '.php';
    private $model_html_writer;
    private $html_writer;
    
    function __construct($controller) {
        $this->controller = $controller;
        $this->model_html_writer = new mvc_HtmlWriter;
        $this->html_writer = new mvc_ModelHtmlWriter($this->controller);
    }
    
    function assign($arg1, $arg2=null) {
        if (is_array($arg1)) {
            $values = $arg1;
        }
        else {
            $values = array($arg1 => $arg2);
        }
        foreach ($values as $name => $value) {
            $this->controller->$name = $value;
        }
    }
    
    function assign_by_ref($name, $value) {
        $this->controller->$name =& $value;
    }
    
    function display($path, $options=null) {
        if (!$options) $options = array();
        else if (!is_array($options)) {
            $options = array('id' => $options);
        }
        $mark_template = gv($options, 'mark_template', true);
        
        $path = str_replace(MM_LIB . '/', '', $path);
        $fullPath = mm_expandPath($path);
        if (!$fullPath) {
            throw new Exception("Template file not found in path: \"$path\"");
        }
        $visible_path = $path;
        if ($visible_path[0] == '/') {
            $visible_path = "[external]/" . basename($visible_path);
        }
        if ($mark_template) {
            echo "<!-- BEGIN: " . h($visible_path) . " -->\n";
        }
        $r = include $fullPath;
        if ($mark_template) {
            echo "<!-- END: " . h($visible_path) . " -->\n";
        }
    }
    
    function register_function($name, $func) {
        // Makes this class compatible with smarty support
    }
    
    function register_compiler_function($name, $func, $option=false) {
        // Makes this class compatible with smarty support
    }
    
    function __call($func, $args) {
        return call_user_func_array(array($this->controller, $func), $args);
    }
    
    function truncate($str, $max, $etc='...') {
        if (strlen($str) > $max) {
            return substr($str, 0, $max - strlen($etc)) . $etc;
        }
        else {
            return $str;
        }
    }
    
    function __get($name) {
        return $this->controller->$name;
    }
    
    function __set($name, $value) {
        $this->controller->$name = $value;
    }

}
