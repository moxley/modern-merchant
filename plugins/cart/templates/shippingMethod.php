<?php
/**
 * @package cart
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
$shipping_methods = $this->getShippingMethods();
?>
<?php if (count($shipping_methods) > 1): ?>
    <?php foreach ($shipping_methods as $i=>$method): ?>
    <label for="cart_shipping_method_id_<?php ph($method->id); ?>">
        <?php ph($method->name); ?> (<?php ph($this->price($method->calculateAmount($this->cart))); ?>)
    </label>
    <?php echo $this->radioButton('cart[shipping_method_id]', $method->id); ?>
    <br />
    <?php endforeach; ?>
<?php else: ?>
<?php echo $this->hiddenFieldTag('cart[shipping_method_id]', $shipping_methods[0]->id)?>
<p>This order will be shipped via <?php ph($this->shipping_methods[0]->name); ?></p>
<?php endif; ?>
