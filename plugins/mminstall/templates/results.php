<?php
/**
 * @package mminstall
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
?>
<h3>Results</h3>
<div style="margin-bottom: 20px">

    <table class="tests">
        <?php foreach ($this->checker->results as $result) { ?>
        <tr>
            <td class="test-title">
                <?php echo sanitize($result->title) ?>
            </td>
            <td class="test-result">
                <?php if ($result->warn): ?><span class="warn">FAIL</span><?php else: ?>
                <?php echo $result->pass ? '<span class="pass">PASS</span>' : '<span class="fail">FAIL</span>' ?>
                <?php endif ?>
                <?php echo ($result->error_msg ? "<br />" . sanitize($result->error_msg) : "");  ?>
            </td>
        </tr>
        <?php } ?>
    </table>

    <?php if (!$this->checker->isPass()): ?>
    <a href="<?php ph($_SERVER['REQUEST_URI']) ?>">Try again</a>
    <?php endif ?>
</div>
