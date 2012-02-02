<?php
/**
 * @package setting
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * Settings controller
 */
class setting_Controller extends admin_Controller
{
    private $dao;
    
    function __construct() {
        parent::__construct();
        $this->dao = new setting_SettingDAO;
    }
    
    function runListAction()
    {
        $this->title = "Settings";
        $this->settings = $this->dao->find();
    }
    
    function runEditAction()
    {
        $this->title = "Edit Setting";
        $this->setting = $this->requireSetting();
    }
    
    function requireSetting()
    {
        $id = $this->getRequiredParam('id');
        $setting = $this->dao->fetch($id);
        if (!$setting)
        {
            throw new Exception("No match found for id='$id'");
        }
        return $setting;
    }
    
    function runUpdateAction()
    {
        $this->setting = $this->requireSetting();
        $this->setting->admin_values = $this->req('setting');
        $this->setting->save();
        
        $this->addNotice("Update setting \"{$this->setting->name}\"");
        $this->redirectToAction('setting');
        return false;
    }
    
    function runDefaultAction()
    {
        $this->title = "Settings";
        $this->setForward('setting.list');
    }
    
    function preViewFilter($action)
    {
        parent::preViewFilter($action);
        $this->nav_template = "setting/_nav";
    }
}
