<?php

class mailing_List extends mvc_Model
{
    const TABLE = "mm_mailing_list";
    
    public $id;
    public $name;
    public $is_public = false;
    
    function getFormFields() {
        $fields = parent::getFormFields();
        $field =& $fields->itemAtName('list[name]');
        $field['label'] = "List Name";
        unset($fields['list[created_on]']);
        return $fields;
    }
    
    function setAdminValues($values) {
        $this->name = gv($values, 'name');
        $this->is_public = (boolean) gv($values, 'is_public');
    }
    
    function beforeAdd() {
        $this->created_on = time();
    }
    
    function getSize() {
        if (!$this->id) return 0;
        return mvc_Model::count('mailing_Subscription', array('where' => array('list_id=?', $this->id)));
    }
}
