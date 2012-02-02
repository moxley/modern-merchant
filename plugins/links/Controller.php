<?php
/**
 * @package links
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * @package links
 */
class links_Controller extends mvc_Controller
{
    function runDefaultAction()
    {
        $this->setForward('links.browse');
    }
    
    function runBrowseAction() {
        $lc_dao = new links_LinkCategoryDAO;
        $new_category = new links_LinkCategory;
        $new_category->id = 'new';
        $new_category->name = "New Links";
        $this->categories = array_merge(array($new_category), $lc_dao->find(array('order' => 'name')));
        $this->links = array();
        if ($category_id = $this->req('link_category_id')) {
            $dao = new links_LinkDAO;
            if ($category_id == 'new') {
                $this->category = $new_category;
                $this->links = $dao->find(array('where' => 'approved=1', 'order' => 'created_on DESC', 'limit' => 5));
            }
            else {
                $this->category = $lc_dao->fetch($category_id);
                $this->links = $dao->find(array('where' => array("approved=1 AND category_id=?", $category_id), 'order' => 'created_on'));
            }
        }
    }
    
    function runSubmitAction() {
        $this->link = mvc_Model::instance('links_Link');
        if ($this->is_post) {
            $this->link->setSubmitterValues($this->req('link'));
            if ($this->link->submit()) {
                $this->redirectToAction('links.submitted');
                return false;
            }
            else {
                $this->addWarnings($this->link->errors);
            }
        }
    }
    
    function runSubmittedAction() {
        // Empty
    }
    
    function runClickAction() {
        $dao = new links_LinkDAO;
        $this->link = $dao->fetch($this->req('id'));
        $this->link->counter++;
        $this->link->save();
        $this->redirect($this->link->url);
        return false;
    }
}
