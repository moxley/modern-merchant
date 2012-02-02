<?php
/**
 * @package category
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * Administrator controller for managing product categories.
 */
class category_Controller extends admin_Controller
{
    private $dao;
    
    function __construct()
    {
        $this->dao = new category_CategoryDAO;
    }
    
    /**
     * Controller action that determines and forwards to the default
     * action.
     *
     * @return string The default action
     */
    function runDefaultAction()
    {
        $this->redirectToAction('product.list');
        return false;
    }
    
    /**
     * Controller action that sets up the "Add new category" form.
     *         
     * @return string  The forwarding action or view
     */
    function runNewAction()
    {
        $this->category = new category_Category;
        $this->category->parent_id = $this->req('parent_id');
        $this->target_action = 'category.add';
        $this->title = "New Category";
    }
    
    /**
     * Controller action to add a new category.
     *         
     * @return string  The forwarding action or view
     */
    function runAddAction()
    {
        $this->category = new category_Category($this->req('category'));
        if (!$this->category->save()) {
            $this->addWarnings($this->category->errors);
            $this->setTemplate('category/new');
            return;
        } else {
            $this->addNotice("Category has been added");
            $this->redirectToAction('product.list');
            return false;
        }
    }
        
    /**
     * Controller action to display a list of the categories.
     *         
     * @return string  The forwarding action or view
     */
    function runListAction()
    {
        // pack results to Outputs
        $dao = new category_CategoryDAO;
        $this->categories = $this->dao->getChildren();
        if ( !$this->categories ) {
            $this->addWarning("No categories found");
        } else {
            $this->category_id = $this->req('category_id');
        }
        $this->title = "Categories";
    }
    
    /**
     * Controller action to display the "Edit Category" form.
     *
     * @return string  The forwarding action or view
     */
    function runEditAction()
    {
        if ($this->is_post) {
        }
        else {
            $this->requireCategory(true);
            $this->title = "Edit Category";
            $this->target_action = 'category.update';
        }
    }
    
    /**
     * Controller action to update a category.
     *         
     * @return string  The forwarding action or view
     */
    function runUpdateAction()
    {
        $this->requireCategory();
        $this->category->setPropertyValues($this->req('category'));
        if ($this->category->save()) {
            $this->addNotice("Category successfully updated.");
            $this->redirectToAction('product.list');
            return false;
        }
        else {
            $this->addWarnings($this->category->errors);
            $this->setTemplate('category/edit');
        }
    }
    
    /**
     * Controller action that deletes a category.
     *         
     * @return string  The forwarding action or view
     */
    function runDeleteAction()
    {
        $this->requireCategory();
        $this->category->delete();
        $this->addNotice("Category successfully deleted.");
        $this->redirectToAction('product.list');
        return false;
    }
    
    function requireCategory($forEdit=false)
    {
        $this->id = $this->getRequiredParam('id');
        $this->category = $this->dao->{$forEdit ? 'fetchForEdit' : 'fetch'}($this->id);
        return $this->category;
    }
}
