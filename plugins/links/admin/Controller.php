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
class links_admin_Controller extends admin_Controller {
    function runDefaultAction() {
        $this->setForward('links_admin.list');
    }
    
    function runListAction() {
        $this->title = "Links";
        $dao = new links_LinkDAO;
        $this->count = $dao->count();
        $find_options = array('offset' => $this->offset, 'limit' => $this->max_results, 'order' => 'approved, created_on DESC');
        $find_options['attach_images'] = true;
        $find_options['attach_categories'] = true;
        $this->links = $dao->find($find_options);
        $params = array('action' => "links_admin");
        $this->results_nav = $this->getResultsNav(
            $this->count,
            $this->offset,
            $this->max_results,
            8,
            $params
            );
    }
    
    function runDeleteAction() {
        $dao = new links_LinkDAO;
        $link = $dao->fetch($this->req('id'));
        if ($link->delete($this->req('id'))) {
            $this->addNotice("Deleted link with id '" . $link->id . "'");
        }
        else {
            $this->addWarning("Failed to delete link: " . implode(', ', $link->errors));
        }
        $this->redirectToAction('links_admin.default');
        return false;
    }
    
    function runEditAction() {
        $dao = new links_LinkDAO;
        $this->link = $dao->fetch($this->req('id'));
        if ($this->is_post) {
            $this->link->setAdminValues($this->req('link'));
            
            if (!$this->link->save()) {
                $this->addWarnings($this->link->errors);
            }
            else {
                $this->addNotice("Updated link: " . $this->link->url);
                $this->redirectToAction('links_admin.default');
                return false;
            }
        }
        $this->title = "Edit Link";
    }
    
    function preViewFilter($action)
    {
        parent::preViewFilter($action);
        $this->nav_template = "links/admin/_nav";
    }
}
