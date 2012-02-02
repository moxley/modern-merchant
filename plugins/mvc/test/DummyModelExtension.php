<?php

class mvc_test_DummyModelExtension extends mvc_test_DummyModel {
    function validate() {
        $errors = parent::validate();
        $errors[] = "extension error";
        return $errors;
    }
}
