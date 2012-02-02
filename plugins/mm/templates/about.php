<?php
/**
 * @package mm
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
?>
<style type="text/css">
.name-value_pairs { position: relative; }
.name-value-pairs label {
    width: 150px;
    font-weight: bold;
    display: inline-block;
    text-align: right;
    margin-right: 20px;
}
.name-value-pairs code {
    width: 200px;
}
.name-value-pairs textarea {
    display: block;
    /*float: left;*/
    margin-left: 170px;
}
</style>
<div>
    <div class="name-value-pairs">
        <label>Project Home:</label> <a href="http://www.modernmerchant.org/" target="mmhome">www.modernmerchant.org</a><br />
        <label>Author:</label> <code>Moxley Stratton</code><br />
        
        <label>Version:</label>
        <code><?php ph($this->version); ?></code><br />
        
        <label>License:</label>
        <textarea cols="80" rows="20"><?php ph($this->license); ?></textarea>
    </div>
</div>
