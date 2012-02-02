<?php
/**
 * Data model the represents a single mailing broadcast.
 *
 * @package mailing
 */

/**
 * @package mailing
 */
class mailing_Broadcast extends mvc_Model
{
    const TABLE = "mm_mailing_broadcast";

    public $id;
    public $started_on;
    public $completed_on;
    public $number_attempted = 0;
    public $number_sent = 0;
    public $notes;
    public $from_addr;
    public $subject;
    public $message;
    public $message_file;
    public $is_html = false;
    public $cancelled = false;
    public $_list_ids;

    function getFormFields($options=null) {
        $fields = parent::getFormFields();
        $fields[] = array(
            'name'       => 'broadcast[list_ids]',
            'type'       => 'checkbox_group',
            'label'      => "Send to the following lists:",
            'collection' => mvc_Model::findKeyValues('mailing_List', array('property' => 'name', 'order' => 'name')));
        $fields[] = array('name' => 'broadcast[message_file]', 'type' => 'file', 'label' => 'Upload Message Body');

        if ($options == 'details') {
            $names = array(
                'broadcast[name]',
                'broadcast[from_addr]',
                'broadcast[subject]',
                'broadcast[message]',
                'broadcast[started_on]',
                'broadcast[completed_on]',
                'broadcast[number_attempted]',
                'broadcast[number_sent]',
                'broadcast[cancelled]'
            );
            $fields = $fields->matchNames($names);
        }
        else {
            $names = array(
                'broadcast[name]',
                'broadcast[list_ids]',
                'broadcast[from_addr]',
                'broadcast[subject]',
                'broadcast[message]'
            );
            $fields = $fields->matchNames($names);
        }
        
        $field =& $fields->itemAtName('broadcast[name]');
        $field['label'] = 'Descriptive name of this mailing broadcast';
        $field['default'] = date(mm_getSetting('datetime_format'));
        $field['required'] = true;

        $field =& $fields->itemAtName('broadcast[from_addr]');
        $field['label'] = "From Address";
        $field['default'] = mm_getSetting('site.name') . ' <' . mm_getSetting('sales.notify') . '>';
        $field['required'] = true;

        $field =& $fields->itemAtName('broadcast[subject]');
        $field['default'] = 'Greetings from ' . mm_getSetting('site.name');
        $field['required'] = true;

        $field =& $fields->itemAtName('broadcast[message]');
        $field['default'] = 'Enter your message body here...';
        $field['required'] = true;

        //$field =& $fields->itemAtName('broadcast[is_html]');
        //$field['label'] = 'Message is HTML?';
        
        if ($options == 'details') {
            foreach ($fields as $i=>$f) {
                $field =& $fields->itemAtIndex($i);
                $field['required'] = false;
                $field['type'] = 'data';
            }
            $field =& $fields->itemAtName('broadcast[message]');
            unset($field['description']);
            $fields[] = array('name' => 'broadcast[notes]', 'type' => 'textarea', 'label' => 'Notes');
        }
        
        return $fields;
    }
    
    function setAdminValues($values) {
        $this->name = gv($values, 'name');
        $this->_list_ids = gv($values, 'list_ids', array());
        $this->from_addr = gv($values, 'from_addr');
        $this->subject = gv($values, 'subject');
        $this->message = gv($values, 'message');
        $this->message_file = gv($values, 'message_file');
        $this->is_html = (boolean) gv($values, 'is_html');
    }
    
    function validate() {
        if (!$this->name) $this->addError("Please provide a Name for this mailing broadcast");
        if (!$this->from_addr) $this->addError("Please provide a From: address");
        if (!$this->subject) $this->addError("Please provide an email subject");
        if (!$this->message && !$this->message_file) $this->addError("Please include a message body to send");
    }
    
    function getIsValidForSend() {
        if (!$this->_list_ids) $this->addError("Please select one or more lists");
        return $this->errors ? false : true;
    }
    
    function beforeAdd() {
        $this->sent = isset($this->sent) ? $this->sent : 0;
    }
    
    function send() {
        if (!$this->is_valid_for_send) return false;
        ignore_user_abort(true);
        set_time_limit(0);
        $this->started_on = time();
        if (!$this->save()) return false;
        $recipients = mvc_Model::find('mailing_Recipient', array('from' => 'mm_mailing_recipient AS r, mm_mailing_subscription AS s', 'where' => array("s.recipient_id = r.id AND s.list_id IN (?)", $this->_list_ids)));
        foreach ($recipients as $r) {
            if ($this->cancelled) break;
            // mm_mail($to, $subject, $message, $additional_headers=null, $additional_parameters=null) {
            $mail_success = mm_mail($r->email, $this->subject, $this->message, "From: $this->from_addr");
            $this->reload();
            $this->number_attempted++;
            $this->number_sent += $mail_success ? 1 : 0;
            $this->save();
        }
        $this->completed_on = time();
        return $this->save();
    }
    
    function getListIds() {
        if (isset($this->_list_ids)) {
            return $this->_list_ids;
        }
        else {
            return array();
        }
    }
}
