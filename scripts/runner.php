<?php
/**
 * Script runner.
 *
 * @package scripts
 */

function usage()
{
    echo "Usage: php scripts/runner.php PLUGIN.SCRIPT\n";
    echo "  - SCRIPT is the name of the PHP file, minus the '.php' extension.\n";
}

if (empty($_SERVER['argv'][1])) {
    usage();
}
else {
    include 'init.php';
    $file = str_replace('.', '/', $_SERVER['argv'][1]) . '.php';
    include 'plugins/' . $file;
}

