<?php
/**
 * @package database
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

class db_DatabaseException extends mm_InfrastructureException {
    function __construct($message) {
        parent::__construct($message);
    }
}     
