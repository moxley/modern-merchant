<?php
/**
 * @package sess
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * @package sess
 */
class sess_admin_Controller extends admin_Controller
{
    private $_dao;
    
    function runShowAction() {
        $this->requireSession();
        $this->title = "Session " . $this->session->sid;
    }
    
    function runListAction() {
        $this->sessions = $this->dao->find(array('offset' => 0, 'limit' => 50, 'order' => 'creation_date DESC'));
        $this->title = "Sessions";
    }
    
    function getDao() {
        if (!$this->_dao) {
            $this->_dao = new sess_SessionDAO;
        }
        return $this->_dao;
    }
    
    function requireSession() {
        if ($id = $this->req('id')) {
            $this->session = $this->dao->fetch($id);
            if (!$this->session) {
                throw new Exception("Failed to find session for id '$id'");
            }
        }
        else if ($sid = $this->req('sid')) {
            $this->session = $this->dao->fetchBySid($sid);
            if (!$this->session) {
                throw new Exception("Failed to find session for sid '$sid'");
            }
        }
        else {
            throw new Exception("Missing required parameter 'id' or 'sid'");
        }
        
    }
}
