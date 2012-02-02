<?php
/**
 * @package user
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
?>
<table width="100%" border="0" cellspacing="0" cellpadding="5">
    <tr>
        <td valign="top">
            <form name="userForm" autocomplete="off" method="post" action="?a=<?php echo $this->target_action ?>">
                <input type="hidden" name="id" value="<?php ph($this->user->id) ?>">
                <table width="100%" border="0" cellpadding="2" class="dataTable">
                    <tr> 
                        <td class="formRowTitle">Username</td>
                        <td class="formRowValues">
                            <?php echo $this->textField('user[username]', array('size' => 40)) ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="formRowTitle">Password</td>
                        <td class="formRowValues"> 
                            <?php echo $this->passwordField('user[new_password]', array('size' => 40, 'autocomplete'=>'off')) ?>
                        </td>
                    </tr>
                    <tr> 
                        <td class="formRowTitle">Confirm Password</td>
                        <td class="formRowValues"> 
                            <?php echo $this->passwordField('user[confirm_password]', array('size' => 40, 'autocomplete'=>'off')) ?>
                        </td>
                    </tr>

                    <tr> 
                        <td class="formRowTitle">First Name</td>
                        <td class="formRowValues">
                            <?php echo $this->textField('user[first_name]', array('size' => 40)) ?>
                        </td>
                    </tr>
                    <tr> 
                        <td class="formRowTitle">Last Name</td>
                        <td class="formRowValues"> 
                            <?php echo $this->textField('user[last_name]', array('size' => 40)) ?>
                        </td>
                    </tr>
                    <tr> 
                        <td class="formRowTitle">Email Address</td>
                        <td class="formRowValues"> 
                            <?php echo $this->textField('user[email]', array('size' => 40)) ?>
                        </td>
                    </tr>
                    <?php if ($this->isAdmin()): ?>
                    <tr>
                        <td class="formRowTitle">Access Zones</td>
                        <td class="formRowValues">
                            <?php foreach (mvc_Model::find('access_Access') as $i=>$access): ?>
                            <?php echo $this->hiddenFieldTag("user[access_names][$i]"); ?>
                            <?php echo $this->checkBoxTag("user[access_names][$i]", $access->name, $this->user->hasAccess($access->name), array('id' => "user_access_names_$i")); ?>
                            <label for="user_access_names_<?php ph($i) ?>"><?php ph($access->title) ?></label><br />
                            <?php endforeach ?>
                        </td>
                    </tr>
                    <?php endif ?>
                    <tr>
                        <td colspan="2" align="center"> 
                            <input type="submit" value="Commit" />
                            &nbsp; 
                            <input type="reset" name="" value="Reset">
                            &nbsp;
                        </td>
                    </tr>
                </table>
            </form>
        </td>
    </tr>
</table>
