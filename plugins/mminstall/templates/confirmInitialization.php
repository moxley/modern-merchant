<?php
/**
 * @package mminstall
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

$has_existing = mm_hasExistingInstallation();
if ($has_existing) {
?>
<p>There appears to be an existing installation. Press the "Upgrade" button below to
upgrade the database.</p>
<?php
}
?>