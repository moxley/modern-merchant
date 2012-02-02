<?php
/**
 * @package themes-default
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <title><?php ph($this->getHeadTitle()); ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="stylesheet" href="/mm/themes/default/layout.css" type="text/css" media="screen" title="default" charset="utf-8">
    <link rel="stylesheet" href="/mm/themes/default/pages.css" type="text/css" media="screen" title="default" charset="utf-8">
    <!--[if lte IE 6]>
    <link rel="stylesheet" href="/mm/themes/default/ie6.css" type="text/css" media="screen" title="default" charset="utf-8">
    <![endif]-->
    <?php echo $this->renderHeadContent() ?>
</head>
<body>
    <!-- begin: head -->
    <div id="head">
        <?php $this->dbContent('layout.header'); ?>
    </div>
    <!-- end: head -->

    <table class="layout maincolumns">
        <tbody>
            <tr>
                <td class="layout mainmenu">
                    <div class="mainmenu">
                        <?php $this->dbContent('layout.sidebar') ?>
                    </div>
                </td>
                <td class="layout">
                    <div class="main-content-column <?php ph($this->page_identifiers) ?>">
                        <?php $this->render("mm/shared/messages"); ?>
                    
                        <?php if($this->title): ?>
                        <h2><?php ph($this->title); ?></h2>
                        <?php endif; ?>
                    
                        <?php echo $this->getContent(); ?>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
    
    <div id="foot">
        <?php $this->dbContent('layout.footer') ?>
    </div>
</body>
</html>
