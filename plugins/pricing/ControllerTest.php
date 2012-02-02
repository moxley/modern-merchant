<?php
/**
 * @package pricing
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class pricing_ControllerTest extends PHPUnit_Framework_TestCase
{
    private $controller;
    private $dao;
    
    function setUp()
    {
        $this->gen = new order_SampleGenerator;
        $this->controller = new pricing_Controller;
        $sess = new sess_Session;
        $GLOBALS['MM_SESSION'] = $sess;
        $this->dao = new pricing_PricingDAO;
        $this->dao->deleteAll();
    }
    
    function testAdd()
    {
        $input = array(
            'pricing' => array(
                'name' => 'asdf',
                'type' => 'multiply',
                'value' => '1.0')
        );
        $this->controller->setRequest($input);
        $this->controller->runAddAction();
        $input =& $this->controller->getRequest();
        $this->assertTrue(isset($input['pricing']), 'pricing should be set in request');
        $this->assertTrue(isset($this->controller->pricing), 'pricing should be set in the controller');
        $this->assertTrue($this->controller->pricing->id > 0, 'is should be > 0');
    }
    
    function testUpdate()
    {
        $pricing = $this->gen->makePricing();
        $this->dao->add($pricing);
        $pricing->name = 'new name';
        $assoc = (array) $pricing;
        $req = array('pricing' => $assoc, 'id' => $pricing->id);
        $this->controller->setRequest($req);
        $this->controller->runUpdateAction();
        $fetched = $this->dao->fetch($pricing->id);
        $this->assertEquals($pricing, $fetched);
    }
}
