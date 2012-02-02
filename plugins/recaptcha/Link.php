<?php
/**
 * @package recaptcha
 */
recaptcha_Plugin::requireRecaptcha();

/**
 * Link extension
 *
 * @package recaptcha
 */
class recaptcha_Link extends links_Link {
    public $recaptcha_challenge_field;
    public $recaptcha_resp;
    public $_dao_class = 'links_LinkDAO';
    
    function getFormFields($options=array()) {
        $fields = parent::getFormFields($options);
        $fields = recaptcha_Plugin::getModelFormFields($fields, $options);
        return $fields;
    }

    function validateForAdd() {
        parent::validateForAdd();
        recaptcha_Plugin::validateForAdd();
    }
}

