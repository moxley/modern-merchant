<?php
/**
 * @package mvc
 */

/**
 * Dummy class for testing.
 *
 * @package mvc
 */
class mvc_DummyWithGet extends mvc_Model {

    public $name = 'dummy';

    private $values = array();

    function getId() {
        return 10;
    }

    function getHello($arg) {
        return $arg;
    }

    function setNothing($arg) {
        $this->values['nothing'] = $arg;
    }

    function _getId() {
        return $this->_getId2();
    }

    private function _getId2() {
        return $this->id;
    }

    function values() {
        return $this->values;
    }
}


