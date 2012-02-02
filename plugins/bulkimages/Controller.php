<?php
/**
 * @package bulkimages
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class bulkimages_Controller extends admin_Controller
{
    private $util;
    
    function __construct()
    {
        parent::__construct();
        $this->util = new bulkimages_ImportUtil;
    }

    function runPromptImportAction()
    {
        $this->source_count = $this->util->getSourceFileCount();
        $this->source_path = str_replace(MM_LIB, '${MM_LIB}', $this->util->getSourcePath());
        $this->title = "Bulk Image Import";
    }
    
    function runImportAction()
    {
        $count = 0;
        $this->util->import($count);
        foreach ($this->util->errors as $i=>$error) {
            if ($i >= 10) {
                $this->addWarning("Too many errors. Skipping remaining files.");
                break;
            }
            $this->addWarning($error);
        }
        $this->addNotice($count . ' files imported');
        $this->redirectToAction('bulkimages.promptImport');
        return false;
    }

}
