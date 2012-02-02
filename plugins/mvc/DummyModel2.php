<?php
/**
 * @package mvc
 */

/**
 * Dummy model class, for testing.
 * @package mvc
 */
class mvc_DummyModel2 extends mvc_Model {

    private $values;

    function setBlah($str) {
        $this->values['blah'] = $str;
    }

    function values() {
        return $this->values;
    }
}
