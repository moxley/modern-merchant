<?php

class mailing_admin_Controller extends admin_Controller
{
    public $nav_template = "mailing/admin/_nav";
    
    function runDefaultAction() {
        $this->setForward('mailing_admin.lists');
    }
    
    function runListsAction() {
        $this->title = "Mailing Lists";
        $this->lists = mvc_Model::find('mailing_List', array('order' => 'name'));
    }
    
    function runAddListAction() {
        $this->title = "Create Mailing List";
        $this->list = mvc_Model::instance('mailing_List');
        if ($this->is_post) {
            $this->list->setAdminValues($this->req('list'));
            if (!$this->list->save()) {
                $this->addWarnings($this->list->errors);
            }
            else {
                $this->addNotice("Created list '{$this->list->name}'");
                $this->redirectToAction('mailing_admin.default');
                return false;
            }
        }
    }
    
    function runEditListAction() {
        $this->title = "Edit Mailing List";
        $this->list = mvc_Model::fetch('mailing_List', $this->req('id'));
        if ($this->is_post) {
            $this->list->setAdminValues($this->req('list'));
            if (!$this->list->save()) {
                $this->addWarnings($this->list->errors);
            }
            else {
                $this->addNotice("Updated list '{$this->list->name}'");
                $this->redirectToAction('mailing_admin.default');
                return false;
            }
        }
    }
    
    function runDeleteListAction()
    {
        $this->list = mvc_Model::fetch('mailing_List', $this->req('id'));
        $this->list->delete();
        $this->redirectToAction('mailing_admin');
        return false;
    }
    
    function runRecipientsAction() {
        $this->title = "Recipients";
        $this->recipients = mvc_Model::find('mailing_Recipient', array('offset' => $this->offset, 'limit' => $this->max_results));
    }
    
    function runAddRecipientAction() {
        $this->title = "Add Recipient";
        $this->recipient = mvc_Model::instance('mailing_Recipient');
        if ($this->is_post) {
            $this->recipient->setAdminValues($this->req('recipient'));
            if (!$this->recipient->save()) {
                $this->addWarnings($this->recipient->errors);
            }
            else {
                $this->addNotice("Added recipient '{$this->recipient->email}'");
                $this->redirectToAction('mailing_admin.recipients');
                return false;
            }
        }
    }

    function runEditRecipientAction() {
        $this->title = "Edit Recipient";
        $this->recipient = mvc_Model::fetch('mailing_Recipient', $this->req('id'));
        if ($this->is_post) {
            $this->recipient->setAdminValues($this->req('recipient'));
            if (!$this->recipient->save()) {
                $this->addWarnings($this->recipient->errors);
            }
            else {
                $this->addNotice("Updated recipient '{$this->recipient->email}'");
                $this->redirectToAction('mailing_admin.recipients');
                return false;
            }
        }
    }
    
    public function runDeleteRecipientAction()
    {
        $this->recipient = mvc_Model::fetch('mailing_Recipient', $this->req('id'));
        $this->recipient->delete();
        $this->addNotice("Deleted recipient {$this->recipient->email}");
        $this->redirectToAction("mailing_admin.recipients");
        return false;
    }
    
    function runBroadcastsAction() {
        $this->title = "Mailing Broadcasts";
        $this->broadcasts = mvc_Model::find('mailing_Broadcast', array('order' => 'started_on DESC'));
    }
    
    function runSendBroadcastAction() {
        $this->title = "Send Mailing";
        $this->broadcast = mvc_Model::instance('mailing_Broadcast');
        if ($this->is_post) {
            $this->broadcast->setAdminValues($this->req('broadcast'));
            if ($this->broadcast->send()) {
                $this->addNotice("Mailing was successfully sent");
                $this->redirectToAction("mailing_admin.broadcasts");
                return false;
            }
            else {
                $this->addWarnings($this->broadcast->errors);
            }
        }
    }
    
    function runBroadcastDetailsAction() {
        $this->broadcast = mvc_Model::fetch('mailing_Broadcast', $this->req('id'));
        if ($this->is_post) {
            $m_req = $this->req('broadcast');
            $this->broadcast->notes = $m_req['notes'];
            $this->broadcast->save();
            $this->addNotice("Saved details");
            $this->redirectToAction('mailing_admin.broadcasts');
            return false;
        }
    }
    
    function runDeleteBroadcastAction() {
        $this->broadcast = mvc_Model::fetch('mailing_Broadcast', $this->req('id'));
        $this->broadcast->delete();
        $this->addNotice("Deleted broadcast");
        $this->redirectToAction('mailing_admin.broadcasts');
        return false;
    }
}
