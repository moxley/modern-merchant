<?php
/**
 * @package report
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
?>
<?php if (!$this->totals): ?>
<p>There is no sales data.</p>
<?php else: ?>
<table cellpadding="0" cellspacing="0" class="dataTable" width="100%">
  <tr>
    <td valign="bottom">
      <table border="0" cellpadding="0" cellspacing="0" width="100%" height="150">
        <tr>
          <?php foreach ($this->totals as $i=>$data): ?>
          <td valign="bottom" align="center">
            <img src="<?php echo mm_getConfigValue('urls.plugins') . '/report/images/spacer.gif'; ?>" width="20" height="<?php print intval($data['total']/$this->max * 100) ?>" 
              style="background-color: red; border: 1px solid black;" /><br />
            <div><?php ph(date("M", $data['time'])) ?>
            '<?php ph(date("y", $data['time'])) ?></div>
            <div>$<?php ph(intval($data['total'])) ?></div>
          </td>
          <?php endforeach ?>
        </tr>
      </table>
    </td>
  </tr>
</table>
<?php endif ?>
