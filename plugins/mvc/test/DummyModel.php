<?php

class mvc_test_DummyModel extends mvc_Model {
    function validate() {
        $errors = array();
        $errors[] = "base model error";
        return $errors;
    }
}
