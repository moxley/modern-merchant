<?php
/**
 * @package media
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

class media_Controller extends mvc_Controller
{
    private $dao;
    
    function __construct() {
        $this->dao = new media_MediaDAO;
    }

    function runShowAction() {
        $id = $this->getRequiredParam('id');
        media_Media::renderById($id);
        return false;
    }
}
