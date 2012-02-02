<?php
/**
 * Quick install
 */
if (count($_SERVER['argv']) < 1) {
    die("This can only be accessed from the command line\n");
}

require './init.php';
require dirname(__FILE__) . "/SeleniumTasks.php";

$tasks = new SeleniumTasks;
$tasks->doInstall();
echo "Done.\n";
