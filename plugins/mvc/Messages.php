<?php
/**
 * @package mvc
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class mvc_Messages
{
    public $messages;

    public function __construct() {
        $this->messages = array();
    }

    public function getMessages()
    {
        $tmp = $this->messages;
        $this->messages = array();
        return $tmp;
    }

    public static function getMessagesByType($type) {
        $sess = mm_getSession();
        $msg = $sess->get("messages.$type");
        if (!$msg) return array();
        return $msg->getMessages();
    }

    public static function getErrors() {
        return self::getMessagesByType('error');
    }

    public static function getWarnings() {
        return self::getMessagesByType('warning');
    }

    public static function getNotices() {
        return self::getMessagesByType('notice');
    }
}
