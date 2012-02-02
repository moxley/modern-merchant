<?php
/**
 * @package sess
 * @copyright (C) 2007 AlchemyWest
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
?>
<style type="text/css">
#sess_show label {
    font-weight: bold;
    display: block;
    width: 100px;
}
#sess_show .row {
    margin-bottom: 10px;
}
</style>
<div id="sess_show">

    <div class="row">
        <label>ID:</label> <?php ph($this->session->id) ?>
    </div>
    
    <div class="row">
        <label>Creation Date:</label> <?php ph(mm_datetime($this->session->creation_date)) ?>
    </div>
    
    <div class="row">
        <label>Modify Date:</label> <?php ph(mm_datetime($this->session->modify_date)) ?>
    </div>
    
    <div class="row">
        <label>SID:</label> <?php ph($this->session->sid)?>
    </div>
    
    <div class="row">
        <label>Data:</label>
        <pre><?php ph(preg_replace("/=>\s*/m", '=> ', var_export($this->session->data, true))); ?></pre>
    </div>
</div>
