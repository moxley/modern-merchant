<?php
/**
 * @package cart
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
$this->title = "Your Shopping Cart";
?>
<?php if ($this->cart->lines): ?>
<form name="cartForm" id="cartForm" action="?a=cart.update" method="POST">

    <div class="cart-border">
        <table id="cart" class="cart">
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Qty</th>
                    <th>Each</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($this->cart->lines as $line): ?>
                <tr class="cart-item">
                    <td class="description">
                        <a class="product_title" href="<?php $this->writeUrl(array('name'=>'catalog.product_detail', 'schema'=>'http')); ?>&amp;sku=<?php ph($line->sku); ?>"><?php ph($line->description); ?></a>
                        <div class="product_sku">Product #<?php ph($line->sku); ?></div>
                    </td>
                    <td class="qty">
                        <?php echo $this->textField("cart[quantities_by_id][$line->id]", array('onchange' => 'this.form.submit()', 'class' => 'amount', 'size' => 3)); ?>
                        <?php
                        /*
                        <input type="text" onChange="this.form.submit()" class="number"
                            name="line[<?php ph($line->id); ?>][quantity]"
                            value="<?php ph($line->qty); ?>"
                            size="3">
                        */
                        ?>
                        <a href="?action=cart.remove&amp;sku=<?php ph($line->sku); ?>">remove</a>
                    </td>
                    <td class="each amount" id="each-ie6">
                        <?php ph(mm_price($line->price)); ?>
                    </td>
                    <td class="total amount">
                        <?php ph(mm_price($line->total)); ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <tr>
                    <td align="center" colspan="4">
                        <input type="submit" name="action_cart_update" value="Recalculate" />
                    </td>
                </tr>
                <tr class="summary-item">
                    <td colspan="3" class="description">
                        Subtotal
                    </td>
                    <td class="amount">
                        <?php ph($this->cart->sub_total); ?>
                    </td>
                </tr>
                <?php if ($this->cart->userChoseShippingMethod()): ?>
                <tr class="summary-item">
                    <td colspan="3" class="description">
                        Shipping via <?php ph($this->cart->shipping_method->title) ?>
                    </td>
                    <td class="amount">
                        <?php ph($this->cart->shipping_total); ?>
                    </td>
                </tr>
                <?php if ($this->cart->validForCheckout()): ?>
                <tr class="summary-item cart-total">
                    <td colspan="3" class="description">
                        Total
                    </td>
                    <td class="amount">
                        <?php ph($this->cart->total); ?>
                    </td>
                </tr>
                <?php endif ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="form-buttons">
    <input type="submit" name="action_cart_continueShopping" value="Continue shopping" onclick="history.go(-1);return false;"/>
    <input id="checkOutButton" type="submit" name="action_cart_checkout" value="Check out &gt;&gt;"/>
</div>

</form>
<?php else: ?>
<p class="no-items">
There are no items in your cart.
</p>
<?php endif; ?>
