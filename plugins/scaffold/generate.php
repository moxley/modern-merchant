<?php

/**
 * Generates class file from any available template in mm/code_templates.
 *
 * <code>Example: scripts/generate test myplugin_Test</code>
 *
 * @package scripts
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

include 'init.php';

$argv = $_SERVER['argv'];

function getAvailableTemplates() {
    $templates = glob(MM_LIB . '/plugins/mm/code_templates/*.php');
    foreach ($templates as $i=>$t) {
        $templates[$i] = str_replace('.php', '', basename($t));
    }
    return $templates;
}

if (count($argv) < 2) {
    echo "Usage: scripts/generate GENERATOR CLASS\n";
    echo "Available generators:\n\t";
    echo implode("\n\t", getAvailableTemplates());
    echo "\n";
}
else {
    $generator = new mm_CodeGenerator;
    $template = $argv[1];
    $class = $argv[2];
    foreach ($generator->renderToClass($template, $class) as $file) {
        echo "Generated $file\n";
    }
}
