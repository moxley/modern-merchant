<?php

/**
 * @package auth
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

$output['nav_html'] = '';

?>
<p>
    <h3 style="display:inline">Modern Merchant</h3> version <?php ph(mm_getConfigValue('version')); ?><br />
    Copyright (C) <?php ph(date('Y')); ?> Moxley Stratton
</p>
<p>
    Modern Merchant comes with ABSOLUTELY NO WARRANTY; for details see the <a href="/?a=mm.license">license</a>.
    This is free software, and you are welcome to redistribute it
    under certain conditions; see the <a href="/?a=mm.license">license</a> for details.
</p>

<table width="100%" border="0" cellspacing="0" cellpadding="5" bgcolor="#eeeeee">
    <tr>
        <td valign="top">
            <form name="login" method="post" action="<?php ph($this->getUrl(array('a' => 'auth.login', 'schema' => 'https'))) ?>">
                <?php echo $this->hiddenField('transition'); ?>
                <table width="100%" border="0" cellpadding="2" class="dataTable">
                    <tr>
                        <td class="formRowTitle">Username</td>
                        <td class="formRowValues">
                            <?php echo $this->textField('login[username]'); ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="formRowTitle">Password</td>
                        <td class="formRowValues">
                            <?php echo $this->passwordField('login[password]'); ?>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" align="center">
                            <input type="submit" value="Login" name="Submit" />
                        </td>
                    </tr>
                </table>
            </form>
        </td>
    </tr>
</table>
<script type="text/javascript">
    document.getElementById('login_username').focus();
</script>
