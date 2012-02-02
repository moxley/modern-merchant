<?php
/**
 * Signup actions.
 */
class mailing_Controller extends mvc_Controller
{
    public function runIndexAction()
    {
        $this->setForward('mailing.signup');
    }
    
    public function runSignupAction()
    {
        $this->title = "Signup to receive notifications and updates";
        $this->recipient = mvc_Model::instance('mailing_Recipient');
        if ($this->is_post) {
            $this->recipient->setSignupValues($this->req('recipient'));
            if ($this->recipient->save()) {
                $this->addNotice("Thank you for signing up!");
                $this->redirectToAction('mailing.signup');
                return false;
            }
            else {
                $this->addWarnings($this->recipient->errors);
            }
        }
    }
}
