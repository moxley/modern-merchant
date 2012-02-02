<?php
/**
 * @package mm
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

if (!defined('MM_TEST_MODE')) {
    require_once dirname(__FILE__) . '/../../scripts/test.php';
}

/**
 * @package mm
 */
class mm_TestSuite extends PHPUnit_Framework_TestCase
{    
    public static function main($argv=null)
    {
        if ($argv === null) $argv = $_SERVER['argv'];
        list($tests, $options) = extractOptions($argv);
        
        self::setUpDatabase();

        $suite = new PHPUnit_Framework_TestSuite('Modern Merchant');
        if (!$tests) {
            $suite->addTest(self::suite());
        }
        else {
            foreach ($tests as $testname) {
                $parts = explode('::', $testname);
                $classname = $parts[0];
                $function = @$parts[1];
                $file = str_replace('_', '/', $classname) . '.php';
                include_once $file;
                if ($function) {
                    $test = new $classname($classname);
                    $test->setName($function);
                    $suite->addTest($test);
                }
                else {
                    if (method_exists($classname, 'suite')) {
                        eval("\$suite->addTest($classname::suite());");
                    }
                    else {
                        $suite->addTestSuite($classname);
                    }
                }
            }
        }
        PHPUnit_TextUI_TestRunner::run($suite);
    }
    
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Modern Merchant');
        $plugins_dir = realpath(dirname(__FILE__) . '/..');
        $d = dir($plugins_dir);
        while (($entry = $d->read()) !== false) {
            if ($entry[0] == '.' || !is_dir($plugins_dir . '/' . $entry)) continue;
            $test_files = glob($plugins_dir . "/$entry/*Test.php");
            $test_classes = array();
            foreach ($test_files as $file) {
                $file = str_replace($plugins_dir . "/", '', $file);
                $file = preg_replace('/\.php$/', '', $file);
                $test_classes[] = str_replace('/', '_', $file);
            }
            
            foreach ($test_files as $i=>$file) {
                $class = $test_classes[$i];
                include $file;
                if (method_exists($class, 'suite')) {
                    eval("\$suite->addTestSuite($class::suite());");
                }
                else {
                    $instance = new $class;
                    if ($instance instanceof PHPUnit_Extensions_SeleniumTestCase) {
                        // Do nothing
                    }
                    else {                
                        $suite->addTestSuite($class);
                    }
                }
            }
        }
        
        return $suite;
    }

    public static function setUpDatabase()
    {
    }
    
    public static function runQueries($db, $queries)
    {
        foreach ($queries as $query) {
            if (is_array($query)) {
                self::runQueries($db, $query);
            }
            else {
                $db->execute($query);
            }
        }
    }
    
}
