<?php
/**
 * @package addr
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * @package addr
 */
class addr_AddressDAO extends mvc_DataAccess
{
    public $regular_columns = array('first_name', 'last_name', 'salutation', 'company', 'title', 'address_1', 'address_2', 'phone_day', 'phone_night', 'fax', 'city', 'state', 'zip', 'email', 'country');
}
