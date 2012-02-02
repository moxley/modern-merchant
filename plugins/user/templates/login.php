<?php
/**
 * @package user
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

$this->title = "Login";
?>
<form method="POST" action="?a=user.login">
    <div style="display:none;">
        <input type="hidden" name="transition" value="<?php ph($this->req('transition')) ?>"/>
    </div>
    <div class="row row-first">
        <div class="row-header">
            <h3>Username</h3>
        </div>
        <div class="row-body">
            <?php echo $this->textField('login[username]'); ?>
        </div>
    </div>
    <div class="row">
        <div class="row-header">
            <h3>Password</h3>
        </div>
        <div class="row-body">
            <?php echo $this->passwordField('login[password]'); ?>
        </div>
    </div>
    <div class="row">
        <?php echo $this->submitButton("Login"); ?>
    </div>
    <p>If you do not yet have an account, you will have the opportunity to create one when you check out. Creating an account is optional and allows you to view your orders and store your shipping information with us for fast check-out in the future.</p>
</form>
