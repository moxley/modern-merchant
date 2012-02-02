<?php
/**
 * @package order
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class order_SampleGenerator
{
    private $dg;
    
    function __construct()
    {
        $this->dg = new test_DataGenerator;
    }
    
    function makeCartLine()
    {
        $product = $this->makeProduct();
        if (!$product->save()) {
            throw new Exception("Failed to save product: " . implode(", ", $product->errors));
        }
        $line = new cart_CartLine($product, $this->dg->makePosInt(2));
        return $line;
    }
    
    function makeCartLineXml()
    {
        $dg = new test_DataGenerator;
        return '<line id="' . x($dg->makeString(15)) . '">' .
            //'<product_id>' . x($dg->getPosInt(1000)) . '</product_id>' .
            '<sku>' . x($dg->makeString(8)) . '</sku>' .
            '<price>' . x(number_format($dg->makePrice(50), 2)) . '</price>' .
            '<qty>' . x($dg->makePosInt(9)) . '</qty>' .
            '</line>';
    }
    
    function makeOrder()
    {
        $order = new order_Order;
        $order->creation_date = $this->dg->makeDate();
        $order->order_date = $order->creation_date + (60*60*24);
        $order->modify_username = $this->dg->makeLowerLetters(6);
        $order->ship_total = $this->dg->makeMoney(5);
        $order->ship_date = $order->date + 60*60*24;
        $order->payment_method_id = $this->dg->makePosInt(10);
        $order->shipping_method_id = $this->dg->makePosInt(10);
        $order->tracking = $this->dg->makeUnique(20);
        $order->billing_addr = $this->makeAddress();
        $order->shipping_addr = $this->makeAddress();
        $order->unique_code = $this->dg->makeUnique(12);
        $order->session_id = $this->dg->makeUnique(18);
        $order->cust_comments = $this->dg->makeWords(20);
        $order->notes = $this->dg->makeWords(8);
        $order->payed = true;
        $order->cust_approved = true;
        $order->previous_customer = true;
        
        $order->lines[] = $this->makeCartLine();
        $order->lines[] = $this->makeCartLine();
        $order->lines[] = $this->makeCartLine();
        $order->lines[] = $this->makeCartLine();
        $order->lines[] = $this->makeCartLine();
        return $order;
    }
    
    function makeCart()
    {
        $cart = new cart_Cart;
        $cart->lines[] = $this->makeCartLine();
        $cart->lines[] = $this->makeCartLine();
        $cart->lines[] = $this->makeCartLine();

        $cart->shipping = $this->makeAddress();
        $cart->billing = $this->makeAddress();

        $payment_method = new cart_DummyPaymentMethod;
        $payment_method->save();
        $cart->payment_method = $payment_method;
        
        $shipping_method = new cart_DummyShippingMethod;
        $shipping_method->save();
        $cart->shipping_method = $shipping_method;

        $sess = mm_getSession();
        $cart->sid = $sess->sid;
        $cart->save();

        return $cart;
    }

    function makeProduct()
    {
        $product = new product_Product;
        $product->modify_user = $this->makeUser();
        $product->sku = $this->dg->makeUnique(6);
        $product->name = $this->dg->makeWords(3);
        $product->active = true;
        $product->price = $this->dg->makePrice(30);
        $product->count = 10 + $this->dg->makePosInt(10);
        $product->description = $this->dg->makeWords(10);
        $product->weight = 1.00;
        return $product;
    }

    function makeUser()
    {
        $user = new user_User;
        $user->username = "testuser";
        return $user;
    }

    function makeCategory()
    {
        $cat = new category_Category;
        $cat->name = $this->dg->makeWords(2);
        $cat->description = $this->dg->makeWords(4);
        $cat->image_id = null;
        $cat->sortorder = 0;
        return $cat;
    }
    
    function makeAddress()
    {
        $addr = new addr_Address;
        $dg = new test_DataGenerator;
        $addr->id = $dg->makePosInt(1000);
        $addr->first_name = $dg->makeName();
        $addr->last_name = $dg->makeName();
        $addr->email = $dg->makeEmail();
        $addr->address_1 = $dg->makeAddressLine();
        $addr->address_2 = $dg->makeAddressLine();
        $addr->city = $dg->makeName(15);
        $addr->state = $dg->makeStateCode();
        $addr->zip = $dg->makeZip();
        $addr->country = $dg->makeCountryCode();
        $addr->phone_day = $dg->makePhone();
        $addr->phone_night = $dg->makePhone();
        $addr->company = $dg->makeCompany();
        return $addr;
    }
    
    function makePricing()
    {
        $pricing = new pricing_Pricing;
        $pricing->id = $this->dg->makeIntId();
        $pricing->type = 'add';
        $pricing->value = 1.00;
        $pricing->name = $this->dg->makeName();
        return $pricing;
    }
    
    function makeMedia()
    {
        $media = new media_Media;
        $media->id = $this->dg->makeIntId();
        //$media->owner_type = 'unknown';
        $media->filename = $this->dg->makeName();
        $media->name = $this->dg->makeName();
        $media->description = $this->dg->makeWords(3);
        $media->width = $this->dg->makePosInt(300);
        $media->height = $this->dg->makePosInt(300);
        
        return $media;
    }
}
