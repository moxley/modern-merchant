<?php
/**
 * @package addr
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

class addr_Address extends mvc_Model {
    public $id;
    public $first_name;
    public $middle_name;
    public $last_name;
    public $salutation;
    public $company;
    public $title;
    public $address_1;
    public $address_2;
    public $city;
    public $state;
    public $zip;
    public $country;
    public $email;
    public $phone_day;
    public $phone_night;
    public $fax;
    public $skip_required = false;
    
    function getIsEmpty() {
        $attribs = array('id', 'first_name', 'middle_name', 'last_name',
            'salutation', 'company', 'title', 'address_1', 'address_2',
            'city', 'state', 'zip', 'country', 'email', 'phone_day',
            'phone_night', 'fax');
        foreach ($attribs as $name) {
            if (!empty($this->$name)) return false;
        }
        return true;
    }
    
    function getName() {
        return trim(str_replace('  ', ' ', $this->first_name . ' ' . $this->middle_name . ' ' . $this->last_name));
    }
    
    function getFname() {
        return $this->first_name;
    }
    
    function setFname($name) {
        $this->first_name = $name;
    }
    
    function getLname($name) {
        return $this->last_name;
    }
    
    function setLname($name) {
        $this->last_name = $name;
    }
    
    function getCityStateZip() {
        return $this->city . ', ' . $this->state . '  ' . $this->zip;
    }
    
    function getPhone() {
        return $this->phone_day;
    }
    
    function setPhone($phone) {
        $this->phone_day = $phone;
    }
    
    function getStreetAddress() {
        return trim($this->address_1 . ' ' . $this->address_2);
    }
    
    function getAddress1() {
        return $this->address_1;
    }
    
    function getAddress2() {
        return $this->address_2;
    }
    
    function setAddress1($addr) {
        $this->address_1 = $addr;
    }
    
    function setAddress2($addr) {
        $this->address_2 = $addr;
    }
    
    function setStreetAddress($addr, $skip=true) {
        $this->address_2 = '';
        $this->address_1 = $addr;
    }
    
    function getAddressAsArray() {
        $this->trimStrings();
        $array = array();
        if ($this->company) $array[] = $this->company;
        if ($this->getName()) $array[] = "c/o " . $this->getName();
        if ($this->address_1) $array[] = $this->address_1;
        if ($this->address_2) $array[] = $this->address_2;
        if ($this->city) {
            $array[] = $this->city . ', ' . $this->state . '  ' . $this->zip;
        }
        if ($this->country) {
            $countries = new addr_Countries;
            $array[] = $countries->getTitle($this->country);
        }
        return $array;
    }
    
    function getElectronicAsArray() {
        $array = array();
        if ($this->email) $array[] = $this->email;
        if ($this->phone_day) $array[] = $this->phone_day . " (day)";
        if ($this->phone_night) $array[] = $this->phone_night . " (evening)";
        return $array;
    }

    function validate() {
        parent::validate();
        $this->trimStrings();
        if (!$this->skip_required && !$this->first_name) {
            $this->addError("Please provide a First Name");
        }
        if (!$this->skip_required && !$this->last_name) {
            $this->addError("Please provide a Last Name");
        }
        if (!$this->skip_required && !$this->address_1) {
            $this->addError("Please provide a Street Address");
        }
        if (!$this->skip_required && !$this->city) {
            $this->addError("Please specify a City or Township");
        }
        if (!$this->skip_required && !$this->country) {
            $this->addError("Please specify a Country");
        }
        if ($this->country === 'US' || $this->country === 'CA') {
            if (!$this->skip_required && !$this->state) {
                $this->addError("Please specify a State");
            }
        }

        $this->validateZip();

        return $this->errors;
    }
    
    function validateZip() {
        if ($this->skip_required) {
            return $this->errors;
        }
        if (!($this->country === 'US' || $this->country === 'CA')) {
            return $this->errors;
        }
        if (!$this->zip) {
            $this->addError("Please provide a Zip or Postal code");
        }
        return $this->errors;
    }
    
    function getDao() {
        if (!$this->_dao) {
            $this->_dao = new addr_AddressDAO;
        }
        return $this->_dao;
    }
    
    function __toString() {
        $attrs = array(
            'id',
            'first_name',
            'middle_name',
            'last_name',
            'salutation',
            'company',
            'title',
            'address_1',
            'address_2',
            'city',
            'state',
            'zip',
            'country',
            'email',
            'phone_day',
            'phone_night',
            'fax',
            'skip_required');
        $str = '';
        foreach ($attrs as $name) {
            $str .= "$name: {$this->$name}\n";
        }
        return $str;
    }
    
    function setValuesFromObj($addr)
    {
        $attribs = array('first_name', 'middle_name', 'last_name',
            'salutation', 'company', 'title', 'address_1', 'address_2',
            'city', 'state', 'zip', 'country', 'email', 'phone_day',
            'phone_night', 'fax');
        foreach ($attribs as $name) {
            $this->$name = $addr->$name;
        }
    }
}
