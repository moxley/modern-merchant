<?php
/**
 * @package contact
 * @copyright (C) 2007 AlchemyWest
 * @copyright (C) 2007 Modern Merchant
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

/**
 */
class contact_Contact extends mvc_Model
{   
    public $name;
    public $email;
    public $email_verify;
    public $comment;

    public function validate() {
        if (!$this->name) {
            $this->addError("Please provide your name");
        }
        if (!$this->email) {
            $this->addError("Please provide your email address");
        }
        else {
            if ($this->email != $this->email_verify) {
                $this->addError("Please verify email addresses match.");
            }
        }
        if (!$this->comment) {
            $this->addError("Please provide a comment or question");
        }
        return $this->errors;
    }
    
    public function send() {
        $this->validate();
        $this->validateForAdd();
        if ($this->errors) return false;
        $body = "Name: " . $this->name . "\n"
            . "Email: " . $this->email . "\n"
            . "Comment:--------\n"
            . $this->comment
            . "\n----------------\n";
        return mm_mail(
            mm_getSetting('sales.notify'),
            'Website comment',
            $body,
            "From: " . mm_getSetting('site.noreply'));
    }
    
    function getFormFields($options=null) {
        $fields = array(
            array('name' => 'contact[name]', 'label' => 'Your Name', 'maxlength' => 50, 'required' => false),
            array('name' => 'contact[email]', 'label' => 'Your Email Address', 'maxlength' => 150, 'required' => true),
            array('name' => 'contact[email_verify]', 'label' => 'Please Verify Your Email Address', 'maxlength' => 150, 'required' => true),
            array('name' => 'contact[comment]', 'label' => 'Your Comments/Questions', 'type' => 'textarea', 'size' => '50x5', 'required' => true)
        );
        return $fields;
    }
}
