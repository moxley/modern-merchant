<?php
/**
 * @package sample
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * Sample Controller.
 * @package sample
 */
class sample_Controller extends mvc_PublicController
{
    /**
     * This gets called when the user passes the "a=sample.show" HTTP parameter
     */
    function runShowAction() {
        // Fetch data from database and pass it to the template
        $dao = new sample_SampleDAO;
        $this->samples = $dao->find();              // Get all 'samples'

        $this->title = "'Show' Action";             // Set the page (<h1>) title, and append to the document <title>
        $this->addNotice("Something happened.");    // Send a notice to the user
        $this->greeting = "Hello, world!";          // Send some data to the template
    }
}
