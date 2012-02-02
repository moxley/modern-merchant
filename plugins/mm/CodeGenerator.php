<?php
/**
 * @package mm
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class mm_CodeGenerator
{
    public function renderToClass($tpl, $class) {
        $classes = array($class);
        $files = array();
        foreach ($classes as $class) {
            $parts = explode('_', $class);
            $package = $parts[0];
            ob_start();
            include 'mm/code_templates/' . str_replace('_', '/', $tpl) . '.php';
            $contents = ob_get_contents();
            ob_end_clean();
            $file = MM_LIB . '/plugins/' . str_replace('_', '/', $class) . '.php';
            $fp = fopen($file, 'w');
            fwrite($fp, $contents);
            fclose($fp);
            $files[] = $file;
        }
        return $files;
    }
}
