<?php
/**
 * @package recaptcha
 */
recaptcha_Plugin::requireRecaptcha();

/**
 * Override of the <code>contact_Contact</code> model.
 *
 * @package recaptcha
 */
class recaptcha_Contact extends contact_Contact
{
    function getFormFields($options=null) {
        $fields = parent::getFormFields($options);
        $fields = recaptcha_Plugin::getModelFormFields($fields, $options);
        return $fields;
    }

    function validateForAdd() {
        parent::validateForAdd();
        recaptcha_Plugin::validateForAdd();
    }
}
