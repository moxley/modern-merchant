<?php
/**
 * Place a sample order.
 */
if (count($_SERVER['argv']) < 1) {
    die("This can only be accessed from the command line\n");
}

require './init.php';
require dirname(__FILE__) . "/SeleniumTasks.php";

$tasks = new SeleniumTasks;
$tasks->doOrder();
echo "Done.\n";
