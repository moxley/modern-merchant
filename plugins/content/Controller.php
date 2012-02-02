<?php
/**
 * @package content
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * Content Controller
 * @package content
 */
class content_Controller extends admin_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->dao = new content_ContentDAO;
    }

    function beforeAction($action)
    {
        if ($action == 'show' || $action == 'display') {
            return true;
        }
        return parent::beforeAction($action);
    }

    function runDefaultAction()
    {
        $this->redirectToAction('content.list');
        return false;
    }

    function runListAction()
    {
        $this->items = $this->dao->getList($this->getOffset(), $this->getMaxResults());
        $this->title = "Content List";
    }

    function runNewAction()
    {
        $this->content = new content_Content;
        $this->title = "Create New Content";
    }

    function runAddAction()
    {
        $da = new content_ContentDAO;
        $content = new content_Content($this->req('content'));
        $content->save();
        $this->addNotice('Added Content');
        $this->redirectToAction('content.edit', array('id'=>$content->id));
        return false;
    }
    
    function runEditAction()
    {
        $this->content = $this->requireContent();
        $this->title = "Edit Content";
    }

    function runUpdateAction()
    {
        $this->content = $this->requireContent($this->req('content'));
        $this->content->save();

        $this->addNotice('Content updated');
        $this->setForward('content');
    }

    function runDeleteAction()
    {
        $id = $this->req('id');
        if (!$id) {
            $this->addError("Missing 'id' parameter");
            $this->setReturnAction('content.list');
            return;
        }
        
        $da = new content_ContentDAO;
        $da->delete($id);

        $this->addNotice('Content deleted');
        $this->redirectToAction('content');
        return false;
    }
    
    function runCancelAction()
    {
        $this->redirectToAction('content');
        return false;
    }
    
    function runShowAction()
    {
        $this->theme_type = 'public';
        $this->page_name = $this->getRequiredParam(array('name', 'page'));
        $da = new content_ContentDAO;
        $content = $da->fetchByName($this->page_name);
        if (!$content) {
            $this->addWarning("No content found under name '{$this->page_name}'");
            $this->setTemplate(false);
            return;
        }
        else {
            $this->setRequestValue('content', $content);
            $this->setRequestValue('title', 'Content');
            if ($content->title) {
                $this->title = $content->title;
            }
            $content->renderToOutput($this);
        }
    }
    
    function requireContent($values=array())
    {
        $id = $this->req('id');
        if (!$id) {
            $name = $this->req('name');
            if (!$name) {
                throw new Exception("Missing request parameter 'id' or 'name'");
            }
            $content = $this->dao->fetchByName($name);
        }
        else {
            $content = $this->dao->fetch($id);
        }
        
        if (!$content) {
            throw new Exception("Failed to find content");
        }
        $content->setPropertyValues($values);
        return $content;
    }
    
    function getContentTypeOptions()
    {
        $names = content_Content::$types;
        foreach ($names as $name) {
            $types[$name] = $name;
        }
        return $types;
    }
    
    function preViewFilter($action)
    {
        parent::preViewFilter($action);
        $this->nav_template = "content/_nav";
    }
}
