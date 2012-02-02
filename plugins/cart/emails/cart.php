<?php
/**
 * @package cart
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
?>
=====================================
Cart Detail
-------------------------------------
<?php
$lines = $this->lines;
foreach( $lines as $line )
{
    printf("%s \"%s\" (%d x %s) = %s\n",
        $line->sku, 
        $line->description, 
        $line->qty,
        mm_price($line->price), 
        mm_price($line->total));
}
?>

-------------------------------------
------------------- SubTotal: <?php print mm_price($this->sub_total)."\n" ?>
------------------- Shipping: <?php print mm_price($this->shipping_total)."\n" ?>
---------------------- Total: <?php print mm_price($this->total)."\n" ?>
