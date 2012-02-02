<?php
/**
 * Customer interface navigation links.
 *
 * @package customer
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
?>
Customer Links:
<?php echo $this->linkTag('list', '?a=customer_admin.list') ?> |
<?php echo $this->linkTag('new', '?a=customer_admin.new') ?>

<form method="get" action="" style="margin-top: 0.5em">
    <input type="hidden" name="a" value="customer_admin.search"/>
    <input type="text" name="q"/>
    <input type="submit" value="Search"/>
    (Single-word search: First Name, Last Name, Username, Email)
</form>
