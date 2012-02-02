<?php
/**
 * @package mvc
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class mvc_PublicRequestParser extends mvc_RequestParser
{
    function __construct($url=NULL)
    {
        parent::__construct();
        $this->setUrl($url);
    }
}
