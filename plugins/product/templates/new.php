<?php
/**
 * @package product
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
?>
<div id="product_edit">

    <?php $this->render('product/_head'); ?>

    <form id="product_edit_form" name="product_edit_form" method="post" action="?a=product.add" enctype="multipart/form-data">
        <div>
            <?php
            $this->render('product/_buttons');
            $this->render('product/_form');
            $this->render('product/_buttons');
            ?>
        </div>
    </form>
</div>
