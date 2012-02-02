<?php
/**
 * @package mm
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
?>
<?php
$errors = $this->getErrors(true);
$warnings = $this->getWarnings(true);
$notices = $this->getNotices(true);
?>
<?php if (count($errors) > 1):
    $errors = $errors;
?>
<div class="messages error">
<span class="messageIntro">The following problems occurred:</span>
<ul>
<?php foreach ($errors as $msg): ?>
  <li><?php ph($msg); ?></li>
<?php endforeach; ?>
</ul></div>
<?php endif; ?>
<?php if (count($errors) == 1): ?>
<div class="messages error">Error: <?php ph($errors[0]); ?></div>
<?php endif; ?>

<?php if (count($warnings) > 1):
    $warnings = $warnings; ?>
<div class="messages warning">
<span class="messageIntro">The following problems were found:</span>
<ul>
<?php foreach ($warnings as $msg): ?>
  <li><?php ph($msg); ?></li>
<?php endforeach; ?>
</ul></div>
<?php endif; ?>
<?php if (count($warnings) == 1): ?>
<div class="messages warning"><?php ph($warnings[0]); ?></div>
<?php endif; ?>

<?php if (count($notices) > 1):
    $notices = $notices; ?>
<div class="messages notice">
<span class="messageIntro"></span>
<ul>
<?php foreach ($notices as $msg): ?>
  <li><?php ph($msg); ?></li>
<?php endforeach; ?>
</ul></div>
<?php endif; ?>

<?php if (count($notices) == 1): ?>
<div class="messages notice"><?php ph($notices[0]); ?></div>
<?php endif; ?>
