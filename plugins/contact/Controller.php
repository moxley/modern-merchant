<?php
/**
 * @package contact
 * @copyright (C) 2007 AlchemyWest
 * @copyright (C) 2007 Modern Merchant
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

/**
 * Contact Us Form Controller.
 */
class contact_Controller extends mvc_PublicController
{
    /**
     * This gets called when the user passes the "a=contact" HTTP parameter
     */
    function runDefaultAction() {
        $this->title = "Contact Us";             // Set the page (<h1>) title
        $this->contact = mvc_Model::instance('contact_Contact');
        $this->contact->setPropertyValues($this->req('contact'));
        
        if ($this->is_post) {
            if ($this->contact->send()) {
                $this->redirectToAction('contact.sent');
                return false;
            }
            else {
                $this->addWarnings($this->contact->errors);
            }
        }
    }
    
    /**
     * No action except to display the view.
     */
    function runSentAction() {
        $this->title = "Contact Us";
    }
}
