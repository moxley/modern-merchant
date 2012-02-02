<?php
/**
 * @package theme
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class theme_Controller extends admin_Controller
{
    function runListAction()
    {
        $this->title = "Themes";
        $root_path = mm_getConfigValue('filepaths.themes');
        $dir = dir($root_path);
        $skip_items = array('.', '..', 'CVS', '.svn');
        $this->themes = array();
        while (false !== ($entry = $dir->read())) {
            if (in_array($entry, $skip_items) || !is_dir($root_path . '/' . $entry)) continue;
            $this->themes[] = $entry;
        }
        $this->title = "Themes";
    }

    function runSelectAction()
    {
        $name = $this->request['name'];
        if ($this->themeIsAdmin($name)) {
            mm_setSetting('theme.admin', $name);
            $this->addNotice("'$name' is now the selected admin theme");
        }
        else {
            mm_setSetting('theme.public', $name);
            $this->addNotice("'$name' is now the selected public theme");
        }

        $this->redirectToAction('theme.list');
        return false;
    }

    function themeIsAdmin($name)
    {
        return endswith($name, '.admin');
    }

    function runEditAction()
    {
        $theme = $this->request['name'];
        if (!$theme) {
            throw new Exception("Missing request parameter 'theme'");
        }
        $base = mm_getConfigValue('filepaths.themes');
        $file = "$base/$theme/info.ini";
        if (!file_exists($file)) {
            throw new Exception("Theme '$theme' does not have an info.ini file");
        }
        $this->ini_array = parse_ini_file($file);
        $this->title = "Theme Details";
        $this->nav_template = "theme/_nav";
    }
}
