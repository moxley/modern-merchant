<?php
/**
 * @package mm
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class mm_FileAccessException extends mm_InfrastructureException {
    public $path;
    function __construct($path) {
        parent::__construct("Failed to write file \"$path\"");
        $this->path = $path;
    }
}

