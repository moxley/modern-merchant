<?php
/**
 * @package cart
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
?>
<?php $cart= mm_getCart(); ?>
<?php if ($cart->lines): ?>
<div id="smallcart">
    <div class="cart-lines">
        <table class="layout">
            <?php foreach ($cart->lines as $line): ?>
            <tr class="cart-item">
                <td class="layout description">
                    <a href="<?php $this->writeUrl(array('type'=>'productDetail', 'sku'=>$line->sku)); ?>"><?php ph($this->truncate($line->description, 20, "...")); ?></a>
                </td>
                <td class="layout amount">
                    <?php ph($line->qty); ?> @ <?php ph($line->price); ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <div class="cart-totals">
        <table class="layout">
            <tr>
                <td class="layout description">
                    Subtotal:
                </td>
                <td class="layout amount">
                    <?php ph($cart->sub_total); ?>
                </td>
            </tr>
            <?php if ($cart->shipping_method_id): ?>
            <tr>
                <td class="layout description">
                    Shipping:
                </td>
                <td class="layout amount"><?php ph($cart->shipping_total); ?></td>
            </tr>
            <?php endif ?>
            <?php if ($cart->validForCheckout()): ?>
            <tr>
                <td class="layout description">Total:</td>
                <td class="layout amount"><?php ph($cart->total); ?></td>
            </tr>
            <?php endif ?>
        </table>
    </div>

    <div class="links">
        <?php if ($cart->validForCheckout()): ?>
        <a href="<?php $this->writeUrl(array('a'=>'cart.checkout', 'schema'=>'https')) ?>">to checkout &gt;&gt;</a>
        <?php else: ?>
        <a href="<?php $this->writeUrl(array('a'=>'cart.cart', 'schema'=>'https')) ?>">to checkout &gt;&gt;</a>
        <?php endif ?>
    </div>

</div>

<?php else: ?>
<p id="smallcart-no-items">There are no items in your cart.</p>
<?php endif; ?>
