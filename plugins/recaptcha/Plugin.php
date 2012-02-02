<?php
/**
 * @package recaptcha
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * @package recaptcha
 */
class recaptcha_Plugin extends plugin_Base
{
    function info() {
        return array(
            'title'    => "reCAPTCHA Plugin",
            'version'  => '0.1',
            'author'   => "Moxley Stratton",
            'url'      => 'http://www.modernmerchant.org/',
            'auto_install' => false
        );
    }
    
    static function requireRecaptcha() {
        require_once dirname(__FILE__) . '/recaptchalib.php';
        require_once dirname(__FILE__) . '/recaptchaetc.php';
    }
    
    static function recaptchaPublicKey() {
        return mm_getSetting('plugins.recaptcha.public_key');
    }
    
    static function recaptchaPrivateKey() {
        return mm_getSetting('plugins.recaptcha.private_key');
    }
    
    function init() {
        // Executed upon each request
        mvc_Hooks::extendModel('links_Link', 'recaptcha_Link');
        mvc_Hooks::extendModel('contact_Contact', 'recaptcha_Contact');
    }
    
    function install() {
        mm_setSetting('plugins.recaptcha.public_key', 'blank');
        mm_setSetting('plugins.recaptcha.private_key', 'blank');
        return TRUE;
    }
    
    function uninstall() {
        mm_removeSetting('plugins.recaptcha.public_key');
        mm_removeSetting('plugins.recaptcha.private_key');
        return TRUE;
    }
    
    /**
     * This is called from the model overrides.
     */
    function getModelFormFields($fields, $options=null) {
        if ($options != 'admin') {
            $public_key = recaptcha_Plugin::recaptchaPublicKey();
            $error = "";
            $fields[] = array('type' => 'html', 'label' => 'reCAPTCHA', 'required' => true, 'content' => recaptcha_get_html($public_key, $error));
        }
        return $fields;
    }
    
    /**
     * This is called from the model overrides.
     */
    function validateForAdd() {
        if (!isset($_POST['recaptcha_challenge_field'])) {
            $this->addError("No 'recaptcha_challenge_field' field was detected");
        }
        else {
            if ($this->recaptcha_challenge_field != $_POST["recaptcha_challenge_field"]) {
                $this->recaptcha_challenge_field = $_POST["recaptcha_challenge_field"];
                $this->recaptcha_resp = recaptcha_check_answer(recaptcha_Plugin::recaptchaPrivateKey(),
                                                $_SERVER["REMOTE_ADDR"],
                                                $_POST["recaptcha_challenge_field"],
                                                $_POST["recaptcha_response_field"]);
            }
            if (!$this->recaptcha_resp->is_valid) {
                $this->addError("Incorrect CAPTCHA words. Please try again.");
            }
        }
    }
}
