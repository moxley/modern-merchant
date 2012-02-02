<?php
/**
 * @package themes-default-admin
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
        <title><?php ph($this->window_title); if(MM_DEMO_MODE) { ph(" - Demo mode"); } ?></title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        
        <link rel="stylesheet" href="/mm/themes/default.admin/layout.css" type="text/css" media="screen" title="default" charset="utf-8">
        <link rel="stylesheet" href="/mm/themes/default.admin/pages.css" type="text/css" media="screen" title="default" charset="utf-8">
        <?php echo $this->renderStylesheetInclude(mm_getConfigValue('urls.themes') . 'default.admin/jscookmenu/theme.css') ?>
        <link rel="stylesheet" href="/mm/themes/default.admin/jscookmenu/theme.css" type="text/css" media="screen" title="default" charset="utf-8">

        <?php echo $this->renderHeadContent() ?>

        <script type="text/javascript">
            var cmThemeOfficeBase = '<?php echo mm_getConfigValue('urls.themes') ?>default.admin/jscookmenu/';
        </script>
        <script type="text/javascript" src="/mm/themes/default.admin/jscookmenu/theme.js"></script>
    </head>

<body bgcolor="#FFFFFF" text="#000000">

  <!-- Page banner -->
  <table border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr class="appTitle">
      <td class="left">
          Modern Merchant
          <?php if(MM_DEMO_MODE) { ph(" - Demo mode"); } ?>
      </td>
      <td class="right" align="right">
        <div style="margin-right: 5px">
            <span class="date" style="margin-right: 5px"><?php echo mm_datetime(time()); ?></span>
            <a
                href="<?php ph(mm_getConfigValue('urls.catalog.script')) ?>"
                style="margin-right: 10px">website</a>
<?php
    if (!$this->isAdmin()) {
?>
        <a href="<?php ph($this->adminBaseUrl()) ?>?a=auth.prompt">login</a>
<?php
    } else {
?>
        <a href="<?php ph($this->adminBaseUrl()) ?>?a=auth.logout">logout</a>
<?php
    }
?>
        </div>
      </td>
    </tr>
  </table>
<?php
    if ($this->isAdmin()) {
?>
<div class="menubackgr">
  <div id="myMenuID">
<script type="text/javascript" language="Javascript">
<!--
var myMenu =
[
<?php
$admin_menu = mvc_Hooks::getMenu('admin');

$menus_js = array();
if (isset($admin_menu)) {
    foreach ($admin_menu->children as $menu) {
        $menus_js[] = $menu->toJS();
    }
}
echo implode(",\n_cmSplit,\n", $menus_js);
?>
];
cmDraw ('myMenuID', myMenu, 'hbr', cmThemeOffice, 'ThemeOffice');
-->
</script>
  </div>
</div>
<?php
    }
?>

<?php $this->showThemeTemplate("messages.php"); ?>

<!-- BEGIN MAIN CONTENT -->
<div id="content" class="<?php ph($this->page_identifiers) ?>">
<?php if ($this->nav_template): ?>
    <div class="nav">
        <?php $this->render($this->nav_template) ?>
    </div>
<?php endif ?>
<?php if ($this->title) echo "<h1>" . h($this->title) . "</h1>" ?>
<?php echo $this->getContent(); ?>
<?php if (@$this->main) $this->render($this->main); ?>

<br style="clear: both" />

</div>
<!-- END MAIN CONTENT -->

    <div class="statusLine">
<?php
    if( isset($this->SESS_Username) )
    {
?>
          User: <?php print $this->SESS_Username ?> &nbsp;
<?php
    }
    if( isset($this->DBMS) )
    {
?>
            Database Engine: <?php print $this->DBMS ?>
<?php
    }
?>
     &nbsp;
    </div>

  </body>
</html>
