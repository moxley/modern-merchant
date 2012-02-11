<?php
/**
 * The configuration file.
 *
 * Set up global constants and global dynamic configuration settings
 * This script should be called by init.php
 *
 * @package configuration
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

// Set up the $MM_CONFIG hash
$_MM_CONFIG = array(); // Temporary holder
$_MM_CONFIG['version']                     = '@version@';
$_MM_CONFIG['environment']                 = 'live';
$_MM_CONFIG['kernel.active']               = true;
$_MM_CONFIG['timezone']                    = "America/Los_Angeles";

$_MM_CONFIG['rewrites.enabled']            = "@rewrites.enabled@";

// Database
$_MM_CONFIG['database.name']               = "@database.name@";
$_MM_CONFIG['database.user']               = "@database.user@";
$_MM_CONFIG['database.password']           = "@database.password@";
$_MM_CONFIG['database.host']               = "@database.host@";
$_MM_CONFIG['database.port']               = "@database.port@";
$_MM_CONFIG['database.type']               = "@database.type@";

// Test Database
$_MM_CONFIG['database.test.name']          = "@database.name@_test";
$_MM_CONFIG['database.test.user']          = "@database.user@";
$_MM_CONFIG['database.test.password']      = "@database.password@";
$_MM_CONFIG['database.test.host']          = "@database.host@";
$_MM_CONFIG['database.test.port']          = 3306;
$_MM_CONFIG['database.test.type']          = "mysql";

$_MM_CONFIG['emails.enabled']              = TRUE;

// File paths
$_MM_CONFIG['filepaths.mm_lib']            = dirname(dirname(__FILE__));
$_MM_CONFIG['filepaths.docs']              = $_MM_CONFIG['filepaths.mm_lib'] . '/docs';
$_MM_CONFIG['filepaths.plugins']           = $_MM_CONFIG['filepaths.mm_lib'] . '/plugins';
$_MM_CONFIG['filepaths.media']             = $_MM_CONFIG['filepaths.mm_lib'] . '/public/media';
$_MM_CONFIG['filepaths.public']            = $_MM_CONFIG['filepaths.mm_lib'] . '/public';
$_MM_CONFIG['filepaths.private']           = $_MM_CONFIG['filepaths.mm_lib'] . '/private';

// Themes
$_MM_CONFIG['filepaths.themes']            = $_MM_CONFIG['filepaths.mm_lib'] . '/themes';
$_MM_CONFIG['theme.public']                = 'default';
$_MM_CONFIG['theme.admin']                 = 'default.admin';

// Logging
$_MM_CONFIG['debug.logging']               = "@debug.logging@";
$_MM_CONFIG['debug.show_exception_trace']  = "@debug.show_exception_trace@";
$_MM_CONFIG['filepaths.logs']              = $_MM_CONFIG['filepaths.private'];
$_MM_CONFIG['filepaths.general_log']       = $_MM_CONFIG['filepaths.logs'] . '/mm.log';
$_MM_CONFIG['log.sql']                     = true;
$_MM_CONFIG['log.emails']                  = true;
$_MM_CONFIG['email_errors']                = "@debug.email_errors@";

// No trailing slash
$_MM_CONFIG['urls.http']                   = "@urls.http@";
$_MM_CONFIG['urls.https']                  = "@urls.https@";

// URIs need to have trailing slash if they are directories
$_MM_CONFIG['urls.site.home']              = '/';
$_MM_CONFIG['urls.mm_root']                = '@urls.mm_root@';
$_MM_CONFIG['urls.public.script']          = $_MM_CONFIG['urls.mm_root'];
$_MM_CONFIG['urls.admin.script']           = $_MM_CONFIG['urls.mm_root'] . 'admin.php';
$_MM_CONFIG['urls.mm_lib']                 = $_MM_CONFIG['urls.mm_root'];
$_MM_CONFIG['urls.themes']                 = $_MM_CONFIG['urls.mm_lib'] . 'themes/';

// Catalog URLs
$_MM_CONFIG['urls.catalog.script']         = $_MM_CONFIG['urls.public.script'];
$_MM_CONFIG['urls.catalog.product_detail'] = $_MM_CONFIG['urls.catalog.script'] . '?a=catalog.productDetail';
$_MM_CONFIG['urls.catalog.product_list']   = $_MM_CONFIG['urls.catalog.script'] . '?a=catalog.products';

$_MM_CONFIG['urls.pages.script']           = $_MM_CONFIG['urls.public.script'];

// Cart URLs
// TODO Remove these. They're used by PayPal.php and WriteUrl.php
$_MM_CONFIG['urls.cart.thank_you']         = $_MM_CONFIG['urls.public.script'] . '?a=cart.postOrderPage';
$_MM_CONFIG['urls.cart.show']              = $_MM_CONFIG['urls.public.script'] . '?a=cart.show';
$_MM_CONFIG['urls.cart.add']               = $_MM_CONFIG['urls.public.script'] . "?a=cart.add";
$_MM_CONFIG['urls.cart.update']            = $_MM_CONFIG['urls.public.script'] . '?a=cart.update';
$_MM_CONFIG['urls.cart.cancel_payment']    = $_MM_CONFIG['urls.public.script'] . '?a=cart.cancelPayment';
$_MM_CONFIG['urls.cart.remove_sku']        = $_MM_CONFIG['urls.public.script'] . '?a=cart.removeSku';

$_MM_CONFIG['urls.media']                  = $_MM_CONFIG['urls.mm_lib'] . 'public/media/';
$_MM_CONFIG['urls.plugins']                = $_MM_CONFIG['urls.mm_lib'] . 'plugins/';
$_MM_CONFIG['urls.image_stream.product']   = $_MM_CONFIG['urls.mm_lib'] . 'webscripts/product_image.php';

// Template paths
$_MM_CONFIG['templates.shared']            = 'mm/shared';

// Controllers
$_MM_CONFIG['controllers.default']         = 'catalog';

// Actions
$_MM_CONFIG['actions.catalog.default']     = 'catalog.products';
$_MM_CONFIG['actions.admin_default']       = 'product.list';
$_MM_CONFIG['actions.admin_login']         = 'auth.prompt';
$_MM_CONFIG['actions.default']             = $_MM_CONFIG['actions.catalog.default'];

// Model
$_MM_CONFIG['model.max_category_depth']    = 10;

// User Interface settings
$_MM_CONFIG['ui.admin_max_list_results']   = 30;
$_MM_CONFIG['ui.admin_max_page_links']     = 8;

// Session settings
$_MM_CONFIG['session.admin']               = 'MM_ADMIN';
$_MM_CONFIG['session.customer']            = 'MM';

// Formatting
$_MM_CONFIG['date_format']                 = "m/d/Y"; // Overridden in settings
$_MM_CONFIG['datetime_format']             = "m/d/Y \a\\t g:i:s a"; // Overridden in settings

// mm_HttpPoster
$_MM_CONFIG['http_poster.method']          = 'php'; // 'php' or 'curl'

// Messages
$_MM_CONFIG['error.internal']              = 'Internal Error';
$_MM_CONFIG['error.product.update']        = 'Failed to update product';
$_MM_CONFIG['error.file.operation']        = 'Failed file operation';
$_MM_CONFIG['error.file.permission']       = 'Failure due to file permission';
$_MM_CONFIG['error.file.noexist']          = 'File does not exist';
$_MM_CONFIG['error.file.type']             = 'Invalid file type';
$_MM_CONFIG['error.db.query']              = 'Database query error';

//
// Testing
//

// Suppress output (for testing)
$_MM_CONFIG['supress_output'] = FALSE;

// Suppress outgoing emails (for testing)
$_MM_CONFIG['supress_emails'] = FALSE;

global $MM_CONFIG;
if (!isset($MM_CONFIG)) $MM_CONFIG = array();
foreach ($_MM_CONFIG as $key=>$value) {
    if (!array_key_exists($key, $MM_CONFIG)) {
        $MM_CONFIG[$key] = $value;
    }
}
