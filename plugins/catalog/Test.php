<?php
/**
 * @package category
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class catalog_Test extends PHPUnit_Framework_TestCase
{
    function setUp() {
        $this->controller = new catalog_Controller;
    }
    
    function testTruth() {
        
    }
    
    //function testProducts() {
    //    $this->controller->request['id'] = 1;
    //    $this->controller->request['action'] = 'catalog.products';
    //    $this->runRequest();
    //}
    //
    //function runRequest() {
    //    ob_start();
    //    try {
    //        $this->controller->runRequest();
    //    }
    //    catch (Exception $ex) {
    //        ob_end_clean();
    //        throw $ex;
    //    }
    //    ob_end_clean();
    //}
    //
    //function testSaveSampleImages() {
    //    $plugin = new catalog_Plugin;
    //    $plugin->saveSampleImages();
    //}
}
