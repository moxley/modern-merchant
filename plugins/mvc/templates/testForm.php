<?php
/**
 * @package mvc
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
?>
<h1>Test Form</h1>

<p>This is for testing the HTML writer utilities</p>
    
<form method="POST" action="javascript:;">
    <p>textFieldTag():<br />
        <?php echo $this->textFieldTag('name', 'Hello'); ?>
        (Hello)
    </p>
    <p>
        textField():<br />
        <?php echo $this->textField('name', array('size' => 40)); ?>
        (Hello, from runTestFormAction()!)
    </p>
    <fieldset>
        <legend>Product</legend>
        <p>SKU:<br />
            <?php echo $this->textField('product[sku]'); ?> (abc123)
        </p>
        <p>Title:<br />
            <?php echo $this->textField('product[title]'); ?> (Test Product)
        </p>
        <p>Price:<br />
            <?php echo $this->textField('product[price]'); ?> (9.99)
        </p>
    </fieldset>
    <fieldset>
        <legend>Check Boxes</legend>
        <p>Red: <?php echo $this->checkBox('product[red]'); ?> (checked)</p>
        <p>Green: <?php echo $this->checkBox('product[green]'); ?> (not-checked)</p>
        <p>Blue: <?php echo $this->checkBox('product[blue]'); ?> (not-checked)</p>
    </fieldset>
    <fieldset>
        <legend>Radio Buttons</legend>
        <p>
            <?php echo $this->radioButton('product[available]', true); ?>
            Available (checked)<br />

            <?php echo $this->radioButton('product[available]', false); ?>
            Not Available (not checked)<br />
        </p>
    </fieldset>
    <p>Select
        <select name="product[style]">
            <option value="">-- Select --</option>
            <?php echo $this->selectOptions('product[style]', array('leather'=>'Leather', 'plastic' => 'Plastic'))?>
        </select>
        (Plastic)
    </p>
            
</form>
