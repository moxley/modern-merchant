<?php
/**
 * @package cart
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
?>
<table width="100%" cellpadding="3" cellspacing="2" border="0" class="dataTable">
    <tr>
        <td valign="top">
            <div><b>Billing Information</b></div>
            <?php $billing_url = h($this->getCartActionUrl(array('action'=>'cart.billingPage'))); ?>
            <?php
            foreach ($this->cart->billing->address_as_array as $line) {
                echo h($line) . "<br />\n";
            }
            if (!$this->cart->complete) {
                echo "<a href=\"$billing_url\">edit</a><br />\n";
            }
            ?>
            <br />
            <?php
            foreach ($this->cart->billing->electronic_as_array as $line) {
                echo h($line) . "<br />\n";
            }
            if (!$this->cart->complete) {
                echo "<a href=\"$billing_url\">edit</a><br />\n";
            }
            ?>
        <br />
        <div><b>Payment</b></div>
        <?php
        if (!$this->cart->payment_method) {
            echo "None";
        }
        else {
            ph($this->cart->payment_method->public_title);
            echo "<br />";
            $payment_url = h($this->getCartActionUrl(array('action'=>'cart.paymentPage')));
            if (!$this->cart->complete) {
                echo "<a href=\"$payment_url\">edit</a><br />\n";
            }
        }
        ?>
    </td>
    <td valign="top">
      <div><b>Shipping Information</b></div>
        <?php $shipping_url = h($this->getCartActionUrl(array('action'=>'cart.shippingPage'))); ?>
        <?php
        foreach ($this->cart->shipping->address_as_array as $line) {
            echo h($line) . "<br />\n";
        }
        if (!$this->cart->complete) {
            echo "<a href=\"$shipping_url\">edit</a><br />\n";
        }
        ?>
        <br />
        <?php
        foreach ($this->cart->shipping->electronic_as_array as $line) {
            echo h($line) . "<br />\n";
        }
        if (!$this->cart->complete) {
            echo "<a href=\"$shipping_url\">edit</a><br />\n";
        }
        ?>
      <br />
      <div><b>Delivery</b></div>
      <?php
        ph($this->cart->shipping_method->title);
        if (!$this->cart->complete) {
            echo "<br />";
            echo "<a href=\"$shipping_url\">edit</a><br />\n";
        }
        ?>
    </td>
  </tr>
</table>

<br />

<div class="cart-border">
  <table width="100%" cellpadding="3" cellspacing="2" border="0" class="dataTable">
    <tr> 
      <td colspan="4"><div class="sectionHeader" style="font-weight: bold">Cart Items</div></td>
    </tr>
<?php foreach ($this->cart->lines as $line): ?>
    <tr class="lineItem"> 
      <td marginwidth="12" marginheight="12" class="itemCell">
        <?php ph($line->sku); ?>
      </td>
      <td marginwidth="12" marginheight="12" class="itemCell">
        <?php ph($line->description); ?>
      </td>
      <td>
         <?php ph($line->quantity); ?> x <?php ph($line->price); ?>
      </td>
      <td align="right" class="itemCell">
          <?php ph($line->total); ?>
      </td>
    </tr>
<?php endforeach; ?>
    <tr> 
      <td colspan="3" align="right"><b>Subtotal</b></td>
      <td align=RIGHT class="itemCell">
          <?php ph($this->cart->sub_total); ?>
      </td>
    </tr>
    <tr> 
      <td colspan="3" align="right"><b>Shipping</b></td>
      <td align=RIGHT class="itemCell">
          <?php ph($this->cart->shipping_total); ?>
      </td>
    </tr>
    <tr> 
      <td colspan="3" align="right"><b>TOTAL</b></td>
      <td align=RIGHT class="itemCell">
          <?php ph($this->cart->total); ?>
        </td>
    </tr>
</table>
</div>
