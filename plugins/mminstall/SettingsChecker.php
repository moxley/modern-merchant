<?php
/**
 * @package mminstall
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * @package mminstall
 */
class mminstall_SettingsChecker extends mminstall_Checker {

    public $site_name = "Modern Merchant Storefront";
    public $email = "example@example.com";

    function setValues($values)
    {
        $this->site_name = gv($values, 'site_name', $this->site_name);
        $this->email = gv($values, 'email', $this->email);
    }

    function check() {
        $GLOBALS['MM_SETTING_DAO_ASSOC'] = null;
        $result = new mminstall_CheckerResult("Set Basic Settings");
        mm_setSetting('site.name',              $this->site_name);
        mm_setSetting('orders.notification',    $this->email);
        mm_setSetting('webmaster.notification', $this->email);
        mm_setSetting('sales.notify',           $this->email);
        mm_setSetting('site.noreply',           $this->email);
        $this->addResult($result);
    }
}
