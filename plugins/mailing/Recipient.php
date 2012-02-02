<?php
/**
 * Data model for mailing.
 *
 * @package mailing
 */

/**
 * @package mailing
 */
class mailing_Recipient extends mvc_Model
{
    const TABLE = "mm_mailing_recipient";
    public $_list_ids;
    public $_is_end_user = false;
    
    function setAdminValues($values) {
        $this->setSignupValues($values);
    }
    
    public function setSignupValues($values)
    {
        $this->name = gv($values, 'name');
        $this->email = gv($values, 'email');
        if ($list_id = gv($values, 'list_id')) {
            $this->_list_ids = array($list_id);
        }
        else if ($list_ids = gv($values, 'list_ids')) {
            $this->_list_ids = $list_ids;
        }
        $this->_is_end_user = true;
    }
    
    function beforeAdd() {
        // Ensure that the end user is selecting only public list ids
        if ($this->_is_end_user) {
            $public_ids = mvc_Model::findColumn('mailing_List', array('where' => "is_public = 1"));
            $this->_list_ids = array_intersect($this->_list_ids, $public_ids);
        }
        $this->created_on = time();
    }
    
    function validateForSave() {
        // Check duplicate email address
        if ($this->email) {
            if ($this->id) {
                $r = mvc_Model::find(get_class($this), array('where' => array('email = ? AND id <> ?', $this->email, $this->id)));
            }
            else {
                $r = mvc_Model::find(get_class($this), array('where' => array('email = ?', $this->email)));
            }
            if ($r) {
                $this->addError("The email address '{$this->email}' has already been taken");
            }
        }
    }
    
    function afterSave() {
        // Add subscriptions
        if (isset($this->_list_ids)) {
            $db = mm_getDatabase();
            $db->execute("DELETE FROM mm_mailing_subscription WHERE recipient_id=?", $this->id);
            foreach ($this->_list_ids as $id) {
                $db->execute("INSERT INTO mm_mailing_subscription (list_id, recipient_id) VALUES (?, ?)", array($id, $this->id));
            }
            unset($this->_list_ids);
        }
    }
    
    function getFormFields($form_name=null) {
        $fields = parent::getFormFields();
        unset($fields['recipient[created_on]']);
        if ($this->customer_id) {
            $field =& $fields->itemAtName('recipient[customer_id]');
            $field['label'] = 'Customer ID';
            $field['type'] = 'data';
        }
        else {
            unset($fields['recipient[customer_id]']);
        }
        $fields[] = array(
            'name'       => 'recipient[list_ids]',
            'type'       => 'checkbox_group',
            'label'      => "Subscribed to the following lists:",
            'collection' => mvc_Model::findKeyValues('mailing_List', array('property' => 'name', 'order' => 'name')));
        
        if ($form_name == 'signup') {
            $names = array('recipient[name]', 'recipient[email]');
            $collection = mvc_Model::findKeyValues('mailing_List',
                array('where' => "is_public=1", 'property' => 'name', 'order' => 'name'));
            if ($collection) {
                if (count($collection) == 1) {
                    foreach ($collection as $id=>$name) {
                        $fields[] = array('type' => 'hidden', 'name' => 'recipient[list_id]', 'value' => $id);
                    }
                    $names[] = 'recipient[list_id]';
                }
                else {
                    $field =& $fields->itemAtName('recipient[list_ids]');
                    $field['collection'] = $collection;
                    $names[] = 'recipient[list_ids]';
                }
            }
            $fields = $fields->matchNames($names);
        }
        return $fields;
    }
    
    function getListIds() {
        if (!$this->id) return array();
        $list_ids = array_values(
            mvc_Model::findColumn(
                'mailing_Subscription',
                array('property' => 'list_id', 'where' => array("recipient_id=?", $this->id))));
        return $list_ids;
    }
    
    function getFullAddress() {
        $addr = "";
        if ($this->name) {
            $addr .= preg_replace('/[^a-z0-9 \'\._-]/', '', $this->name) . ' ';
        }
        $addr .= $this->email;
        return $addr;
    }

}
