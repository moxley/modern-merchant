<?php
require 'Testing/Selenium.php';

class SeleniumTasks {
  public $is_upgrade = false;
  
  function setUp() {
    $this->base_url = "http://modern-0-6";
    $this->selenium = new Testing_Selenium(
        "*firefox",
        "{$this->base_url}/"
    );
    $result = $this->selenium->start();
  }
  
  function tearDown() {
    $this->selenium->stop();
  }

  function doUpgrade() {
    $this->is_upgrade = true;
    $this->doInstall();
  }
  
  function doInstall() {
    $this->setUp();
    echo "Installing...\n";
    $this->selenium->open("/mm/install.php");
    $this->selenium->type("urls_https", $this->base_url);
    $this->selenium->click("//input[@value='Set Hostnames >']");
    $this->selenium->waitForPageToLoad("30000");
    $this->selenium->type("database_name", "modern");
    $this->selenium->type("database_user", "modern");
    $this->selenium->type("database_password", "modern");
    $this->selenium->click("//input[@value='Check Connection >']");
    $this->selenium->waitForPageToLoad("30000");
    if ($this->is_upgrade) {
      $this->selenium->click("//input[@value='Upgrade Modern Merchant Plugins >']");
    }
    else {
      $this->selenium->click("//input[@value='Install Plugins and Site Data >']");
    }
    $this->selenium->waitForPageToLoad("30000");
    $this->selenium->click("debug_mode_1");
    $this->selenium->click("//input[@value='Write configuration file >']");
    $this->selenium->waitForPageToLoad("30000");
    if (!$this->is_upgrade) {
      $this->selenium->type("settings_email", "moxley@moxleydata.com");
      $this->selenium->click("//input[@value='Save >>']");
      $this->selenium->waitForPageToLoad("30000");
      //$this->selenium->doCommand('pause', array(null, '500'));
      sleep(1);
      $this->selenium->type("admin_username", "admin");
      $this->selenium->type("admin_new_password", "admin");
      $this->selenium->click("//input[@value='Add Administrator >']");
      $this->selenium->waitForPageToLoad("30000");
    }
    $this->selenium->click("link=here");
    $this->selenium->waitForPageToLoad("30000");
    $this->tearDown();
  }
  
  function doOrder() {
    $this->setUp();
    echo "Placing order...\n";

    $this->selenium->open("/");
    $this->selenium->waitForPageToLoad("30000");
    $cookie = $this->selenium->getCookie();
    $this->selenium->createCookie($cookie, "");
    $this->selenium->click("link=Jewelry");
    $this->selenium->waitForPageToLoad("30000");
    $this->selenium->click("//input[@value='Add to Cart']");
    $this->selenium->waitForPageToLoad("30000");
    sleep(1);
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
    $this->selenium->click("link=Manager");
    $this->selenium->waitForPageToLoad("30000");

    $this->tearDown();
  }
}
