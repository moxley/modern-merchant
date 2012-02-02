<?php
/**
 * @package content
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
?>
<ul>
  <li<?php echo $this->controller_name == 'cart' ? ' class="selected"' : '' ?>>
    <a href="<?php $this->writeUrl(array('a'=>'cart', 'schema'=>'https')) ?>">Cart</a>
  </li>

  <?php if (($user = mm_getUser()) && $user->isAdmin()): ?>
  <li>
    <a href="<?php $this->writeUrl(array('a'=>'admin.default', 'schema' => 'https')) ?>">Manager</a>
  </li>
  <?php endif; ?>

  <?php if ($user = mm_getUser()): ?>
  <li<?php echo $this->controller_name == 'customer' ? ' class="selected"' : '' ?>>
    <a href="<?php $this->writeUrl(array('a'=>'customer.account', 'schema'=>'https')) ?>">Your Account</a>
  </li>
  <?php endif; ?>

  <li<?php echo $this->action_path == 'user.login' ? ' class="selected"' : '' ?>>
    <?php echo $this->loginLogoutLink(array('login' => 'Login', 'logout' => 'Logout')) ?>
  </li>

  <li<?php echo $this->controller_name == 'contact' ? ' class="selected"' : '' ?>>
    <a href="<?php $this->writeUrl(array('a'=>'contact')) ?>">Contact Us</a>
  </li>
</ul>
