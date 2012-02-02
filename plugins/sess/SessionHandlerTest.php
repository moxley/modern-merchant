<?php
/**
 * @package sess
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class sess_SessionHandlerTest extends PHPUnit_Framework_TestCase
{
    function testReferences()
    {
        $handler = new sess_SessionHandler;
        $data = array();
        $handler->setData($data);
        $data['test'] = 123;
        $fetched = $handler->getData();
        $this->assertEquals($data['test'], $fetched['test']);
        $sess = $handler->start();
        $this->assertEquals($data['test'], $sess->get('test'));
    }
    
    function testWriteRead()
    {
        return;
        $sess = new sess_Session;
        $value = 'this is a test';
        $sess->set('test', $value);
        $dao = new sess_SessionDAO;
        $sess_data1 = $dao->formatData($sess->data);
        sess_SessionHandler_write($sess->sid, $sess_data1);
        $sess_data2 = sess_SessionHandler_read($sess->sid);
        $data1 = $dao->parseData($sess_data1);
        $data2 = $dao->parseData($sess_data2);
        $this->assertEquals($data2['test'], $value);
    }
    
    function testDestroy()
    {
        return;
        $sess = new sess_Session;
        $sess->set('test', '123');
        $dao = new sess_SessionDAO;
        $sess_data = $dao->formatData($sess->data);
        sess_SessionHandler_write($sess->sid, $sess_data);
        sess_SessionHandler_destroy($sess->sid);
        $result = sess_SessionHandler_read($sess->sid);
        $this->assertEquals('', $result);
    }
    
    function testGarbage()
    {
        return;
        $sess = new sess_Session;
        $sess->set('test', '123');
        $dao = new sess_SessionDAO;
        $sess_data = $dao->formatData($sess->data);
        sess_SessionHandler_write($sess->sid, $sess_data);
        sess_SessionHandler_gc(-1);
        $result = sess_SessionHandler_read($sess->sid);
        $this->assertEquals('', $result);
    }
}
