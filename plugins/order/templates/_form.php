<?php
/**
 * @package order
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
$order = $this->order;
?>
<form method="post" action="<?php ph($this->adminBaseUrl()) ?>" accept-charset="utf-8">
<?php echo $this->hiddenFieldTag('id', $order->id) ?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="dataTable">
    <tr>
        <td align="center" colspan="2">
            <input type="submit" name="actions[<?php ph($this->target_action) ?>]" value="<?php ph($this->action_label) ?>" />
            <input type="submit" name="actions[order.cancel]" value="Cancel" />
        </td>
    </tr>
<?php
            if( $order->id ) {
?>
    <tr>
        <td class="itemRowTitle" width="150">
            Order ID
        </td>
        <td class="itemRowValues">
            <?php ph($order->id) ?>
            <a href="?action=order.resendToSales&amp;id=<?php ph($order->id) ?>">Send a copy of this order to <?php ph(mm_getSetting('orders.notification')) ?></a>
        </td>
    </tr>
<?php
         }
?>
<?php
            if( $order->cart_id ) {
?>
    <tr>
        <td class="itemRowTitle" width="150">
            Cart ID
        </td>
        <td class="itemRowValues">
            <?php echo $this->linkTag($order->cart_id, mm_actionToUri('cart_admin.show?id=' . $order->cart_id)) ?>
        </td>
    </tr>
<?php
         }
?>
    <tr>
        <td class="itemRowTitle">
            Modified by (User)
        </td>
        <td class="itemRowValues">
            <?php ph($order->modify_username) ?>
        </td>
    </tr>
    <tr>
        <td class="itemRowTitle">
            Creation Date
        </td>
        <td class="itemRowValues">
            <?php ph(mm_datetime($order->creation_date)) ?>
        </td>
    </tr>
    <tr>
        <td class="itemRowTitle">
            Order Date
        </td>
        <td class="itemRowValues">
<?php if( $order->unique_code ) { ph(mm_datetime($order->order_date)); } else { ?>
            <?php echo $this->textField('order[order_date]', array('filter' => 'mm_date')) ?>
            (MM/DD/YYYY)
<?php } ?>
        </td>
    </tr>

    <tr>
        <td class="itemRowTitle">
            Shipping Date
        </td>
        <td class="itemRowValues">
            <?php echo $this->textField('order[ship_date]', array('filter' => 'mm_date')) ?>
            (MM/DD/YYYY)
        </td>
    </tr>
    <tr>
    <tr>
        <td class="itemRowTitle">
            Shipping Method
        </td>
        <td class="itemRowValues">
            <select name="order[shipping_method_id]">
                <option value=""> - Select - </option>
                <?php echo $this->selectOptions('order[shipping_method_id]', $this->shipping_method_options); ?>
            </select>

        </td>
    </tr>
    <tr>
        <td class="itemRowTitle">
            Tracking Number
        </td>
        <td class="itemRowValues">
            <?php echo $this->textField('order[tracking]') ?>
        </td>
    </tr>

    <tr>
        <td class="itemRowTitle">
            Payment Method
        </td>
        <td class="itemRowValues">
            <select name="order[payment_method_id]">
                <option value=""> - Select - </option>
                <?php echo $this->selectOptions('order[payment_method_id]', $this->payment_method_options); ?>
            </select>

        </td>
    </tr>
    <tr>
        <td class="itemRowTitle">
               Approved by customer
        </td>
        <td class="itemRowValues">
            <?php echo $this->checkBox('order[cust_approved]', 'T') ?>
        </td>
    </tr>

    <tr>
        <td class="itemRowTitle">
       Payed
        </td>
        <td class="itemRowValues">
            <?php echo $this->checkBox('order[payed]', 'T') ?>
        </td>
    </tr>

<!-- Order Lines: BEGIN -->
    <tr>
        <td class="itemRowTitle">
            Items
        </td>
        <td class="itemRowValues">
            <table id="item_lines" width="100%" cellpadding="3" cellspacing="1">
                <thead>
                    <tr>
                        <th>SKU</th>
                        <th>Description</th>
                        <th>Price</th>
                        <th>Qty</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($order->lines as $id=>$line): ?>
                    <tr>
                        <td class="dataRowOdd sku">
                            (SKU)
                            <?php ph($line->sku) ?>
                        </td>
                        <td class="dataRowOdd description">
                            <div><?php echo $this->linkTag(h($line->description), array('a' => 'product.edit', 'sku' => $line->sku)) ?></div>
                            <?php echo $this->checkBoxTag("order[lines_with_id][$line->id][delete]", '1') ?>
                            <label for="order_lines_with_id_<?php ph($line->id) ?>_delete_1">Remove line?</label>
                        </td>
                        <td class="dataRowOdd price">
                            <?php echo $this->textFieldTag("order[lines_with_id][$line->id][price]", $line->price, array('size' => 7, 'filter' => 'mm_pricenumber'))?>
                        </td>
                        <td class="dataRowOdd qty">
                            <?php echo $this->textFieldTag("order[lines_with_id][$line->id][qty]", $line->qty, array('size' => 4))?>
                        </td>
                        <td class="dataRowOdd total">
                            <?php ph(mm_pricenumber($line->total)) ?>
                        </td>
                    </tr>
                    <?php endforeach ?>
                    <tr>
                        <td class="dataRowOdd" colspan="5">
                            <input type="text" name="order[lines_with_id][-1][sku]" size="7" value="">
                            <input type="submit" name="actions[<?php ph($this->target_action) ?>]" value="Lookup by SKU" />
                        </td>
                    </tr>
                    <tr>
                        <td colspan="5" align="center">
                            <input type="submit" name="actions[<?php ph($this->target_action) ?>]" value="Update" />
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4" align="right"><b>Sub-Total: </b></td>
                        <td class="dataRowOdd"><?php ph(number_format($order->sub_total, 2)) ?></td>
                    </tr>
                    <tr>
                        <td colspan="4" align="right">
                            <b>Shipping: </b>
                        </td>
                        <td class="dataRowOdd">
                            <?php echo $this->textField('order[ship_total]', array('filter' => 'mm_pricenumber', 'size' => 7)) ?>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4" align="right"><b>Total: </b></td>
                        <td class="dataRowOdd"><?php ph(number_format($order->total, 2)) ?></td>
                    </tr>
                </tbody>

            </table>

        </td>
    </tr>
<!-- Order Lines: END -->
    <tr>
        <td class="itemRowTitle">Ship - First Name</td>
        <td class="itemRowValues">
            <?php echo $this->textField('order[shipping_addr][first_name]') ?>
        </td>
    </tr>
    <tr>
        <td class="itemRowTitle">Ship - Last Name</td>
        <td class="itemRowValues">
            <?php echo $this->textField('order[shipping_addr][last_name]') ?>
        </td>

    </tr>
    <tr>
        <td class="itemRowTitle">Ship - Company</td>
        <td class="itemRowValues">
            <?php echo $this->textField('order[shipping_addr][company]') ?>
        </td>
    </tr>
    <tr>
        <td class="itemRowTitle">Ship - Address 1</td>
        <td class="itemRowValues">
            <?php echo $this->textField('order[shipping_addr][address_1]') ?>
        </td>
    </tr>
    <tr>
        <td class="itemRowTitle">Ship - Address 2</td>
        <td class="itemRowValues">
            <?php echo $this->textField('order[shipping_addr][address_2]') ?>
        </td>
    </tr>
    <tr>
        <td class="itemRowTitle">Ship - City</td>
        <td class="itemRowValues">
            <?php echo $this->textField('order[shipping_addr][city]') ?>
        </td>
    </tr>
    <tr>
        <td class="itemRowTitle">Ship - State</td>
        <td class="itemRowValues">
            <?php echo $this->textField('order[shipping_addr][state]') ?>
        </td>
    </tr>
    <tr>
        <td class="itemRowTitle">Ship - Zip/Postal Code</td>
        <td class="itemRowValues">
            <?php echo $this->textField('order[shipping_addr][zip]') ?>
        </td>
    </tr>
    <tr>
        <td class="itemRowTitle">Ship - Country</td>
        <td class="itemRowValues">
            <select name="order[shipping_addr][country]">
                <option value="">-- Select --</option>
                <option value="US">United States</option>
                <option value="CA">Canada</option>
                <option value="">-- --</option>
                <?php echo $this->selectOptions('order[shipping_addr][country]', addr_Countries::all()) ?>
            </select>
        </td>
    </tr>
    <tr>
        <td class="itemRowTitle">Ship - Email</td>
        <td class="itemRowValues">
            <?php echo $this->textField('order[shipping_addr][email]') ?>
        </td>
    </tr>
    <tr>
        <td class="itemRowTitle">Ship - Phone</td>
        <td class="itemRowValues">
            <?php echo $this->textField('order[shipping_addr][phone]') ?>
        </td>
    </tr>
    <tr>
        <td class="itemRowTitle">Bill - First Name</td>
        <td class="itemRowValues">
            <?php echo $this->textField('order[billing_addr][first_name]') ?>
        </td>
    </tr>
    <tr>
        <td class="itemRowTitle">Bill - Last Name</td>
        <td class="itemRowValues">
            <?php echo $this->textField('order[billing_addr][last_name]') ?>
        </td>
    </tr>
    <tr>
        <td class="itemRowTitle">Bill - Company</td>
        <td class="itemRowValues">
            <?php echo $this->textField('order[billing_addr][company]') ?>
        </td>

    </tr>
    <tr>
        <td class="itemRowTitle">Bill - Address 1</td>
        <td class="itemRowValues">
            <?php echo $this->textField('order[billing_addr][address_1]') ?>
        </td>
    </tr>
    <tr>
        <td class="itemRowTitle">Bill - Address 2</td>
        <td class="itemRowValues">
            <?php echo $this->textField('order[billing_addr][address_2]') ?>
        </td>
    </tr>
    <tr>

        <td class="itemRowTitle">Bill - City</td>
        <td class="itemRowValues">
            <?php echo $this->textField('order[billing_addr][city]') ?>
        </td>
    </tr>
    <tr>
        <td class="itemRowTitle">Bill - State</td>
        <td class="itemRowValues">
            <?php echo $this->textField('order[billing_addr][state]') ?>
        </td>
    </tr>
    <tr>
        <td class="itemRowTitle">Bill - Zip</td>
        <td class="itemRowValues">
            <?php echo $this->textField('order[billing_addr][zip]', array('size' => 10)) ?>
        </td>
    </tr>
    <tr>
        <td class="itemRowTitle">Bill - Country</td>
        <td class="itemRowValues">
            <select name="order[billing_addr][country]">
                <option value="">-- Select --</option>
                <option value="US">United States</option>
                <option value="CA">Canada</option>
                <option value="">-- --</option>
                <?php echo $this->selectOptions('order[billing_addr][country]', addr_Countries::all()) ?>
            </select>
        </td>
    </tr>
    
    <tr>
        <td class="itemRowTitle">Bill - Email</td>
        <td class="itemRowValues">
            <?php echo $this->textField('order[billing_addr][email]') ?>
            <a href="?action=order.resendToCustomer&amp;id=<?php ph($order->id) ?>">Resend order email to <?php ph($this->order->billing_addr->email) ?></a>
        </td>
    </tr>
    <tr>
        <td class="itemRowTitle">Bill - Phone</td>
        <td class="itemRowValues">
            <?php echo $this->textField('order[billing_addr][phone]') ?>
        </td>
    </tr>

    <tr>
        <td class="itemRowTitle">Customer Comments</td>
        <td class="itemRowValues"><pre style="height: 30px; padding: 10px; background-color: white; border: 1px solid #aaa"><?php ph($order->cust_comments) ?></pre></td>
    </tr>
    <tr>
        <td class="itemRowTitle">
            Notes
        </td>
        <td class="itemRowValues">
            <?php echo $this->textArea('order[notes]', array('size' => '60x10', 'wrap' => 'virtual', 'style' => 'width: 100%')) ?>
        </td>
    </tr>
    <tr>
        <td colspan="2" class="itemRowValues" align="center">
            <br /><br />
            <input type="submit" name="actions[<?php ph($this->target_action) ?>]" value="<?php ph($this->action_label) ?>">
            <input type="submit" name="actions[order.cancel]" value="Cancel" />
        </td>
    </tr>
</table>
</form>
