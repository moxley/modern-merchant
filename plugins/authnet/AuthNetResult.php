<?php
/**
 * @package authnet
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

class authnet_AuthNetResult
{
    protected $resp_values;
    protected $data_error = false;

    function passed() {
        return (!$this->data_error && $this->getAN_ResponseCode() == 1 );
    }            
    
    function declined() {
        return (!$this->data_error && $this->getAN_ResponseCode() == 2);
    }
    
    function hasError() {
        return ($this->data_error || $this->getAN_ResponseCode() == 3);
    }
    
    function setRespValues($values) {
        if (!$values) $this->data_error = true;
        if (count($values) < 4) $this->data_error = true;
        $this->resp_values = $values;
    }

    function getAN_ResponseCode() {
        return gv($this->resp_values, 0);
    }

    function getAN_SubCode() {
        return gv($this->resp_values, 1);
    }

    function getAN_ReasonCode() {
        return gv($this->resp_values, 2);
    }
    
    function getReasonCode() {
        return gv($this->resp_values, 2);
    }

    function getAN_ReasonText() {
        if ($this->data_error) return "Cannot contact payment gateway";
        return gv($this->resp_values, 3);
    }

    function getAN_ApprovalCode() {
        return gv($this->resp_values, 4);
    }

    function getUserMessage() {
        return $this->getAN_ReasonText();
    }
}
