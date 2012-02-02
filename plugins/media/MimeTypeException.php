<?php
/**
 * @package media
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

class media_MimeTypeException extends mvc_BusinessException {
    public $type;
    function __construct($type) {
        $this->type = $type;
        parent::__construct("Unknown mime type: $type");
    }
}
