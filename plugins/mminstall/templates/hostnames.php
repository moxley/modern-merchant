<?php
/**
 * @package mminstall
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

global $MM_CONFIG_ORIG;
if ($MM_CONFIG_ORIG) {
    $req['http'] = $MM_CONFIG_ORIG['urls.http'];
    $req['https'] = $MM_CONFIG_ORIG['urls.https'];
}
else {
    $req['http'] = 'http://' . $_SERVER['HTTP_HOST'];
    $req['https'] = 'https://' . $_SERVER['HTTP_HOST'];
}
?>

<h2>Step 1: File Paths</h2>

<?php $this->render('mminstall/results'); ?>

<h2>Step 2: Hostnames</h2>

<p>These values can be changed later, after you install Modern Merchant.
    To do so, make the appropriate changes in <code>mm/conf/config.php</code></p>

<form method="post" action="?a=mminstall.hostnames">
    <table>
        <tr>
            <td class="row-title">
                <label>Your HTTP hostname:</label>
                <div class="help">(Example: http://www.example.com)</div>
            </td>
            <td>
                <?php echo $this->textField('urls[http]'); ?>
            </td>
        </tr>
        <tr>
            <td class="row-title">
                <label>Your HTTPS hostname:</label>
                <div class="help">(Example: <em>https</em>://www.example.com)<br/>
                    If you're not sure whether you have SSL, including an
                    SSL certificate installed, enter 'http' instead
                    of 'https'
                </div>
            </td>
            <td>
                <?php echo $this->textField('urls[https]'); ?>
            </td>
        </tr>
        <tr>
            <td colspan="2" style="text-align: center">
                <input type="submit" value="Set Hostnames &gt;" />
            </td>
        </tr>
    </table>
</form>
