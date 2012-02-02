<?php
/**
 * @package sample
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * @package sample
 */
?>
<p>Greeting: <?php ph($this->greeting) ?></p>

<?php foreach($this->samples as $sample): ?>
<fieldset>
    <legend><?php ph($sample->name) ?></legend>
    <?php ph($sample->comment) ?>
</fieldset>
<?php endforeach ?>
