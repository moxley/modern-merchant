<?php
/**
 * @package mminstall
 */

/**
 * Test the CLI installer
 *
 * @package mminstall
 */
class mminstall_CliTest extends PHPUnit_Framework_TestCase
{
    function setUp()
    {
        $this->cli = new mminstall_CliInstaller;
    }

    function testMissingConfig()
    {
        try {
            $this->cli->setConfigFile(dirname(__FILE__) . '/no_exist.ini');
            $this->cli->fetchConfig();
            $this->fail("Should have thrown mm_Exception");
        }
        catch (mm_Exception $e) {
            // Pass
        }
    }

    function testFetchConfig()
    {
        $this->cli->setConfigFile(dirname(__FILE__) . '/config_install.ini');
        $config = $this->cli->fetchConfig();
        $this->assertType('array', $config);
        $this->assertEquals('test', gv($config, 'database.name'));
    }

    function testConfigFilePropertyAccess()
    {
        $path = dirname(__FILE__) . '/dummy_config.ini';
        $this->cli->setConfigFile($path);
        $this->assertEquals($path, $this->cli->getConfigFile());
    }
}

