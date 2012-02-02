<?php
/**
 * @package catalog
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

//set_include_path(get_include_path() . PATH_SEPARATOR . './PEAR/');
//require 'Testing/Selenium.php';
require_once realpath(dirname(__FILE__) . "/../test/SeleniumTestCase.php");

/**
 */
//class catalog_SeleniumTest extends PHPUnit_Framework_TestCase
class catalog_SeleniumTest extends PHPUnit_Extensions_SeleniumTestCase
{
    public function setUp()
    {
        //$this->selenium = new Testing_Selenium(
        //    "*firefox",
        //    "http://modern-0-6/",
        //    "localhost",
        //    4444,
        //    30000,
        //    'curl'
        //);
        //$this->selenium->start();
        $this->setBrowser("*firefox");
        $this->setBrowserUrl("http://modern-0-6/");
    }

    public function tearDown()
    {
    }
    
    public function testAddToCart()
    {
        $this->open("/");
        $this->assertRegExp("/^Modern Merchant.*/i", $this->getTitle());
        $cookie = $this->selenium->getCookie();
        $this->selenium->createCookie($cookie, "");
        $this->click('xpath=//a[text()="Jewelry"]');
        $this->waitForPageToLoad(10000);
        $this->assertRegExp("/Jewelry/", $this->getTitle());

        $this->click('xpath=//a[text()="Labyrinth Pendant"]');
        $this->waitForPageToLoad(10000);
        $this->assertRegExp("/Labyrinth Pendant/", $this->getTitle());

        $this->click('xpath=//input[@value="Add to Cart"]');
        $this->waitForPageToLoad(10000);
        $this->assertRegExp("/Shopping Cart/", $this->getTitle());
        $this->assertElementPresent('xpath=//a[text()="Labyrinth Pendant"]');

        // Set zip code
        $this->assertElementPresent('xpath=//input[@name="cart[shipping][zip]"]');
        $this->type('xpath=//input[@name="cart[shipping][zip]"]', '97212');

        $this->click('xpath=//input[@value="Check out >>"]');
        $this->waitForPageToLoad(10000);
        $this->assertElementPresent('xpath=//h2[text()="Checkout: Shipping"]');
        $shipping = array(
            'first_name' => 'Moxley',
            'last_name' => 'Stratton',
            'company' => 'Modern Merchant',
            'address_1' => '3606 NE 9th Ave.',
            'address_2' => 'Suite 100',
            'city' => 'Portland',
            'state' => 'OR',
            'country' => 'US',
            'email' => 'moxley@moxleydata.com',
            'phone_day' => '503-381-9155',
            'phone_night' => '503-381-9155'
        );
        foreach ($shipping as $name => $value) {
            $this->type('xpath=//input[@name="shipping[' . $name . ']"]', $value);
        }
        $this->click('xpath=//input[@value="Next -->>"]');
        $this->waitForPageToLoad(10000);
        $this->assertElementPresent('xpath=//h2[text()="Checkout: Shipping"]');
    }
}

if (__FILE__ == realpath($_SERVER['argv'][0])) {
  $suite = new PHPUnit_Framework_TestSuite('catalog_SeleniumTest');
  //$result = new PHPUnit_Framework_TestResult;
  //$suite->run($result);
  $result = PHPUnit_TextUI_TestRunner::run($suite);
}
