<?php
/**
 * @package theme
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
?>
<table class="records">
    <thead>
        <tr>
          <td>Theme</td>
          <td>Type</td>
          <td>Selected</td>
          <td>Info</td>
      </tr>
    </thead>
    <tbody>
    <?php
    $selected_public = mm_getSetting('theme.public');
    $selected_admin = mm_getSetting('theme.admin');
    foreach ($this->themes as $theme) {
        $type = "public";
        if (endswith($theme, '.admin')) $type = "admin";
        $selected = "<a href=\"?action=theme.select&amp;name=$theme\">select</a>";
        if ($theme == $selected_public || $theme == $selected_admin) $selected = "Y";
        echo <<<END_ROW
        <tr>
          <td>$theme</td>
          <td>$type</td>
          <td>$selected</td>
          <td><a href="?action=theme.edit&amp;name=$theme">info</a></td>
        </tr>
END_ROW;
    }
    ?>
    </tbody>
</table>
