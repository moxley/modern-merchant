<?php
/**
 * @package mm
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class mm_NoMatchException extends mvc_BusinessException
{
    function __construct($sub_message) {
        parent::__construct("No match found for: " . $sub_message);
    }
}
