<?php
/**
 * @package content
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
?>
<div class="contentRow">
  <div class="mainContentArea">
  
  <p>Hello, welcome to Modern Merchant!</p>

  <p>
    Here is a list of products from &quot;Jewelry&quot; (category ID 7):<br/>
    <?php $this->getHelper('catalog')->showProducts(7); ?>
  </p>
 
  <h2>How to change this page's content</h2>
  
  <ol>
    <li>Log in in the Manager</li>
    <li>Select 'Content' from the 'Website' menu.
    <li>Edit the content with the name 'home'.</li>
  </ol>
  
  <div style="margin: 15px 20px; overflow: auto;">
    <span style="text-align: center;float:left">
      <img src="<?php ph(mm_getConfigValue('urls.mm_root')) ?>plugins/content/images/login-tab.gif" width="176" height="100" style="border:1px solid #aaa" />
      <br />1
    </span>
    <span style="text-align: center;float:left">
      <img src="<?php ph(mm_getConfigValue('urls.mm_root')) ?>/plugins/content/images/content-menu.gif" width="176" height="100" style="border:1px solid #aaa" />
      <br />2
    </span>
    <span style="text-align: center;float:left">
      <img src="<?php ph(mm_getConfigValue('urls.mm_root')) ?>/plugins/content/images/home-item.gif" width="176" height="100" style="border:1px solid #aaa" />
      <br />3
    </span>
  </div>
  
  <p style="padding-top: 20px">Besides the home page content, you may also change the content of the site's header,
    sidebar, and footer. Just find the content items &quot;layout.header&quot;,
    &quot;layout.sidebar&quot;, and &quot;layout.footer&quot;</p>

  </div><!-- end: mainContentArea -->
</div><!-- end: contentRow -->
