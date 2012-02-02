<?php
/**
 * Tests upgrades from version to version.
 *
 * @package build
 */

if (!$_SERVER['argv'][0]) {
    echo "Usage: php test-upgrades.php START_VERSION [END_VERSION]\n";
    exit(2);
}

set_time_limit(0);
@ob_end_flush();
ob_implicit_flush(true);

require dirname(dirname(__FILE__)) . "/htdocs/mm/plugins/mm/tools.php";
require dirname(__FILE__) . '/prompt.php';

// Test upgrades from earlier to later version of Modern Merchant
$site_home_dir = "/Users/moxley/www/vhosts/mmupgrade";
$htdocs_dir    = "$site_home_dir/htdocs";
$versions_dir  = "$site_home_dir/versions";
$tmp_dir       = "$site_home_dir/tmp";
$build_dir     = dirname(__FILE__);
$dsn = array(
    'hostname' => 'localhost',
    'database' => 'mmupgrade',
    'user'     => 'modern',
    'password' => 'modern'
);

function fix_old_dirs() {
    global $htdocs_dir;
    chmod($htdocs_dir . '/mm/media/items', 0777);
    chmod($htdocs_dir . '/mm/media/categories', 0777);
}

function fix_0_5_0() {
    fix_old_dirs();
}
function fix_0_5_1() {
    fix_old_dirs();
}
function fix_newer_dirs() {
    global $htdocs_dir;
    chmod($htdocs_dir . '/mm/public', 0777);
    chmod($htdocs_dir . '/mm/private', 0777);
    chmod($htdocs_dir . '/mm/conf', 0777);
}
function fix_0_6_0b1() {
    fix_newer_dirs();
}
function fix_0_6_0b2() {
    fix_newer_dirs();
}
function fix_0_6_0b3() {
    fix_newer_dirs();
}

$previous_version_urls = array(
    "http://internap.dl.sourceforge.net/sourceforge/modern/modern-0_03_0.zip",
    //"http://internap.dl.sourceforge.net/sourceforge/modern/modern-0.04.0.zip",
    "http://internap.dl.sourceforge.net/sourceforge/modern/modern-0_04_01.zip",
    //"http://superb-west.dl.sourceforge.net/sourceforge/modern/modern_0_5_0.zip",
    //"http://superb-west.dl.sourceforge.net/sourceforge/modern/modern_0_5_1.zip",
    "http://superb-west.dl.sourceforge.net/sourceforge/modern/modern_0_5_2.zip",
    "http://superb-west.dl.sourceforge.net/sourceforge/modern/modern_0_5_3.zip",
    "http://superb-west.dl.sourceforge.net/sourceforge/modern/modern_0_5_4.zip",
    "http://superb-west.dl.sourceforge.net/sourceforge/modern/modern_0_5_5.zip",
    "http://superb-west.dl.sourceforge.net/sourceforge/modern/modern_0_5_6.zip",
    "http://easynews.dl.sourceforge.net/sourceforge/modern/modern_0.6.0b1.tar.gz",
    "http://internap.dl.sourceforge.net/sourceforge/modern/modern_0.6.0b2.tar.gz",
    "http://internap.dl.sourceforge.net/sourceforge/modern/modern_0.6.0b3.tar.gz"
);
function compile_previous_versions() {
    global $previous_version_urls;
    
    $previous_versions = array();
    // Pull all relevant information from URL
    foreach ($previous_version_urls as $url) {
        $filename = basename($url);
        $match = array();
        preg_match('/^(.*)((\.tar\.gz)|(\.zip))$/', $filename, $match);
        $folder = $match[1];
        $extension = preg_replace('/^\./', '', $match[2]);
        $match = array();
        preg_match('/(\d+)[_.](\d+)[_.](\d+)([a-z]+\d)?/', $folder, $match);
        $version = $match[1] . '.' . $match[2] . '.' . $match[3] . $match[4];
        //echo "filename: $filename, folder: $folder, version: $version, extension: $extension\n";
        $previous_versions[] = array(
            'url'       => $url,
            'filename'  => $filename,
            'folder'    => $folder,
            'extension' => $extension,
            'version'   => $version
        );
    }
    return $previous_versions;
}

//echo var_export(mm_parse_version("0.1.2a3", true)) . "\n";
//echo var_export(mm_parse_version("0.1.2a", true)) . "\n";
//echo var_export(mm_parse_version("0.1.2", true)) . "\n";
//echo var_export(mm_parse_version("0.1", true)) . "\n";
//echo var_export(mm_parse_version("1", true)) . "\n";
//echo var_export(mm_parse_version("0.0", true)) . "\n";

//echo "1.0: next version: " . mm_next_version("1.0") . "\n";
//echo "0.1: next version: " . mm_next_version("0.1") . "\n";
//echo "0.0: next version: " . mm_next_version("0.0") . "\n";
//echo "1.0.1: next version: " . mm_next_version("1.0.1") . "\n";
//echo "1.1.9: next version: " . mm_next_version("1.1.9") . "\n";

//
//echo mm_compare_versions("0.1.2a3", "0.1.2a3") . " (Should be 0)\n";
//echo mm_compare_versions("0.1.2a", "0.1.2a3") . " (Should be -1)\n";
//echo mm_compare_versions("0.1.2", "0.1.2a") . " (Should be 1)\n";

$start_version = $_SERVER['argv'][1];
$end_version = $_SERVER['argv'][2];
$latest_version = trim(file_get_contents(dirname(dirname(__FILE__)) . '/htdocs/mm/conf/version.txt'));

$previous_versions = compile_previous_versions();

// Figure out which versions to install
$versions_to_install = array();
foreach ($previous_versions as $version) {
    if (mm_compare_versions($version['version'], $start_version) >= 0) {
        if (!$end_version || mm_compare_versions($version['version'], $end_version) <= 0) {
            $versions_to_install[] = $version;
        }
    }
}

// Tack on the current version, if necessary
if (!$end_version || $end_version == $latest_version) {
    $versions_to_install[] = array(
        'url'       => 'local',
        'filename'  => 'local',
        'folder'    => 'modern_' . $latest_version,
        'version'   => $latest_version
    );
}

function install_version($version) {
    global $dsn;
    global $htdocs_dir;
    global $versions_dir;
    global $build_dir;
    get_version($version);
    //echo `rm -rf $htdocs_dir`;
    echo `cd $versions_dir; cp -pR $version[folder]/* $htdocs_dir/; if [ -f $version[folder]/.htaccess ]; then cp $version[folder]/.htaccess $htdocs_dir; fi`;
    $func = "fix_" . str_replace('.', '_', $version['version']);
    if (function_exists($func)) {
        $func();
    }
}

function get_version($version) {
    global $versions_dir, $build_dir;
    if ($version['url'] == 'local') {
        echo "Building $version[version]\n";
        $package_dir = $versions_dir . '/' . $version['folder'];
        if (file_exists($package_dir)) {
            echo `rm -rf $package_dir`;
        }
        echo `cd $versions_dir && $build_dir/package.sh .`;
    }
    else {
        if (!file_exists($versions_dir . '/' . $version['folder'])) {
            if (!file_exists($versions_dir . '/' . $version['filename'])) {
                echo "Downloading {$version['filename']}\n";
                echo `cd $versions_dir && wget {$version['url']}`;
            }
            echo "Unpacking {$version['filename']}\n";
            if (mm_compare_versions($version['version'], "0.5.0") < 0) {
                // Create a directory to unpack the contents
                echo `cd $versions_dir; mkdir $version[folder]`;
                echo `cd $versions_dir/$version[folder] && unzip ../{$version['filename']}`;
            }
            else if ($version['extension'] == "zip") {
                echo `cd $versions_dir && unzip {$version['filename']}`;
            }
            else {
                echo `cd $versions_dir && tar xzvf {$version['filename']}`;
            }
        }
        else {
            echo "Folder exists already: {$version['folder']}\n";
        }
    }
}

// Download and extract versions
echo `rm -rf $htdocs_dir`;
echo `mkdir $htdocs_dir`;
echo `$build_dir/droptables $dsn[database]`;
$cmdline = new prompt();
foreach ($versions_to_install as $version) {
    
    $buffer = $cmdline->get("-- Press ENTER to install version $version[version] --");
    if ($buffer === "exit" || $buffer === "\004") break;
    
    install_version($version);
    echo "Installed files for version $version[version]\n";
}
