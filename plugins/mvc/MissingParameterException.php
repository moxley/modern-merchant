<?php
/**
 * @package mvc
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class mvc_MissingParameterException extends Exception {
    function __construct($param_name) {
        if (!is_array($param_name)) {
            parent::__construct("Missing required parameter: $param_name");
        }
        else {
            $names = $param_name;
            if (count($names) > 1) {
                $names[count($names)-1] = "or " . $names[count($names)-1];
            }
            $str = implode(', ', $names);
            parent::__construct("Missing required parameter: $str");
        }
    }
}
