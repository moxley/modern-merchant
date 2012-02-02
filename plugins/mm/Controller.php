<?php
/**
 * @package mm
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class mm_Controller extends admin_Controller
{
    function beforeAction($action)
    {
        $anonymous = array('license', 'about');
        if (in_array($action, $anonymous)) return true;
        return parent::beforeAction($action);
    }
    
    function runAboutAction()
    {
        $this->version = mm_getConfigValue('version');
        $this->license = file_get_contents(mm_getConfigValue('filepaths.docs'). '/LICENSE.TXT');
        $this->title = "About Modern Merchant";
    }
    
    function runLicenseAction()
    {
        $this->title = "Modern Merchant - License";
    }

    function runNotesAction()
    {
        $this->title = "Release Notes";
    }
    
    function runTesterAction()
    {
        $this->title = "PHP Code Tester";
        if ($this->is_post) {
            $this->code = $this->req('code');
            ob_start();
            eval($this->code);
            $this->execute_output = ob_get_clean();
        }
    }
}
