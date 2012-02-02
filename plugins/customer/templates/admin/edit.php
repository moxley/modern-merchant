<?php
/**
 * @package customer
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
?>
<form method="POST" action="?a=customer_admin.update&amp;id=<?php ph($this->customer->id) ?>">
    <div class="row">
        <label>Customer ID</label>
        <?php ph($this->customer->id) ?>
    </div>
    <?php $this->render('customer/admin/_form') ?>
    <input type="submit" value="Update" />
</form>
