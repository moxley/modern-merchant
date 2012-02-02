<?php
/**
 * @package mm
 */

if (!defined('MM_TEST_MODE')) {
    require_once dirname(__FILE__) . '/../../scripts/test.php';
}

require_once realpath(dirname(__FILE__) . "/../test/SeleniumTestCase.php");
//require_once 'Testing/Selenium.php';

/**
 * @package mm
 */
class mm_AcceptanceTest extends PHPUnit_Extensions_SeleniumTestCase
{
    function setUp()
    {
        $this->base_url = "http://modern-0-6-2";
        //$this->verificationErrors = array();
        //$this->selenium = new Testing_Selenium(
        //    "*firefox",
        //    "{$this->base_url}/"
        //);
        //$result = $this->selenium->start();
        
        $this->setBrowser("*firefox");
        $this->setBrowserUrl("{$this->base_url}/");
        
        $this->image_dir = realpath(dirname(__FILE__) . "/../media/test");
    }

    function tearDown()
    {
        if (isset($this->selenium)) {
            $this->selenium->stop();
        }
    }

    function testInstall()
    {
        // Skip this test
        return;
        $this->runInstaller();
        $this->runLogin();
    }
    
    function testBuyKitten()
    {
        $this->runLogin();
        $this->addKittens();
        $this->runLogout();
        $this->buyKitten();
    }
    
    function runLogin()
    {
        $this->selenium->open(mm_getConfigValue('urls.mm_root'));
        $this->selenium->click("link=Login");
        $this->selenium->waitForPageToLoad("30000");
        //$this->selenium->doCommand('pause', array(null, '500'));
        sleep(1);
        $this->selenium->type("login_username", "admin");
        $this->selenium->type("login_password", "admin");
        $this->selenium->click("//input[@value='Login']");
        $this->selenium->waitForPageToLoad("30000");
    }
    
    function runLogout()
    {
        $this->selenium->open(mm_getConfigValue('urls.mm_root') . "?a=auth.logout");
        $this->selenium->waitForPageToLoad("30000");
    }

    function runInstaller()
    {
        $this->selenium->open(mm_getConfigValue('urls.mm_root') . "mminstall.php");
        $this->selenium->type("urls_https", $this->base_url);
        $this->selenium->click("//input[@value='Set Hostnames >']");
        $this->selenium->waitForPageToLoad("30000");
        $this->selenium->type("database_name", "mminstall");
        $this->selenium->type("database_user", "mminstall");
        $this->selenium->type("database_password", "modern");
        $this->selenium->click("//input[@value='Check Connection >']");
        $this->selenium->waitForPageToLoad("30000");
        $this->selenium->click("//input[@value='Install Plugins and Site Data >']");
        $this->selenium->waitForPageToLoad("30000");
        $this->selenium->click("debug_mode_1");
        $this->selenium->click("//input[@value='Write configuration file >']");
        $this->selenium->waitForPageToLoad("30000");
        $this->selenium->type("settings_email", "moxley@moxleydata.com");
        $this->selenium->click("//input[@value='Save >>']");
        $this->selenium->waitForPageToLoad("30000");
        //$this->selenium->doCommand('pause', array(null, '500'));
        sleep(1);
        $this->selenium->type("admin_username", "admin");
        $this->selenium->type("admin_new_password", "admin");
        $this->selenium->click("//input[@value='Add Administrator >']");
        $this->selenium->waitForPageToLoad("30000");
        $this->selenium->click("link=here");
        $this->selenium->waitForPageToLoad("30000");
    }

    function addKittens()
    {
        /**
         * Add "Kittens" Category
         */
        $this->selenium->open(mm_getConfigValue('urls.mm_root') . "?a=product.list");
        $this->selenium->click("//a[contains(@href, '" . mm_getConfigValue('urls.mm_root') . "?a=category.new&parent_id=4')]");
        $this->selenium->waitForPageToLoad("30000");
        $this->selenium->type("category[name]", "Kittens");
        $this->selenium->type("category[description]", "Kittens are cute!");
        $this->selenium->click("actions[category.add]");
        $this->selenium->waitForPageToLoad("30000");
        
        /**
         * Add Kitten 1
         */
        $this->_addKitten(array('name' => 'Kitten 1'));
        
        /**
         * Add Kitten 2
         */
        $this->_addKitten(array('name' => 'Kitten 2'));
    }
    
    function _addKitten($options=array())
    {
        $this->selenium->click("//a[contains(@href, '" . mm_getConfigValue('urls.mm_root') . "?a=product.new&category_id=9')]");
        $this->selenium->waitForPageToLoad("30000");
        $name = gv($options, 'name', "Kitten 1");
        $this->selenium->type("product_name", $name);
        $this->selenium->type("product[description]", "This is $name");
        $this->selenium->click("product_active_1");
        $this->selenium->type("product_count", "1");
        $this->selenium->type("product_price", "30.00");
        $this->selenium->type("product_weight", "1");
        $this->selenium->click("link=Add image...");
        $this->selenium->click("//input[@value='Save']");
        $this->selenium->waitForPageToLoad("30000");
    }
    
    function buyKitten()
    {
        //$cookie = $this->selenium->getCookie();
        //$this->selenium->createCookie($cookie, "");
        $this->selenium->open(mm_getConfigValue('urls.mm_root'));
        $this->selenium->waitForPageToLoad("30000");
        $this->selenium->click("link=Kittens");
        $this->selenium->waitForPageToLoad("30000");
        $this->selenium->click("link=Kitten 1");
        $this->selenium->waitForPageToLoad("30000");
        //$this->selenium->doCommand('pause', array(null, '500'));
        sleep(1);
        $this->selenium->click("//input[@value='Add to Cart']");
        $this->selenium->waitForPageToLoad("30000");
        $this->selenium->type("cart_shipping_zip", "97212");
        $this->selenium->click("checkOutButton");
        $this->selenium->waitForPageToLoad("30000");
        sleep(1);
        $this->selenium->type("cart_shipping_first_name", "Moxley");
        $this->selenium->type("cart_shipping_last_name", "Stratton");
        $this->selenium->type("cart_shipping_company", "Moxley Data Systems");
        $this->selenium->type("cart_shipping_address_1", "123 Main St.");
        $this->selenium->type("cart_shipping_address_2", "Suite 100");
        $this->selenium->type("cart_shipping_city", "Portland");
        $this->selenium->type("cart_shipping_state", "OR");
        $this->selenium->select("cart[shipping][country]", "label=United States");
        $this->selenium->type("cart_shipping_email", "moxley@moxleydata.com");
        $this->selenium->type("cart_shipping_phone_day", "503-381-9155");
        $this->selenium->click("//input[@value='Next -->']");
        $this->selenium->waitForPageToLoad("30000");
        $this->selenium->click("cart_payment_method_id_1");
        $this->selenium->type("cart[comments]", "*** This is a test! ***");
        $this->selenium->click("submit");
        $this->selenium->waitForPageToLoad("30000");
        $this->selenium->click("submit");
        $this->selenium->waitForPageToLoad("30000");
        $this->selenium->click("//input[@value='Send Payment']");
        $this->selenium->waitForPageToLoad("30000");
        
        $this->viewOrder();
    }
    
    function testOrder()
    {
        $this->viewOrder();
    }
    
    function viewOrder()
    {
        $this->runLogin();
        $this->selenium->open(mm_getConfigValue('urls.mm_root') . "?a=order.list");
        $this->selenium->waitForPageToLoad("30000");
        
        $this->selenium->click("link=Edit");
        $this->selenium->waitForPageToLoad("30000");
        
        $this->assertChecked("//input[@name='order[payed]' and @type='checkbox']");
        $this->assertEquals("PayPal IPN", $this->getSelectedLabel("//select[@name='order[payment_method_id]']"));
        $first_line = "id('item_lines')/tbody/tr[1]";
        $this->assertElementContainsText("xpath=$first_line/td[contains(@class, 'description')]/*/a", "Kitten 1");
        $this->assertElementValueEquals("xpath=$first_line/td[contains(@class, 'price')]//input[1]", "30.00");
    }
}
