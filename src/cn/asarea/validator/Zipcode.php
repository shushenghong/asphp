<?php

namespace cn\asarea\validator;

/**
 * US Zipcode validator
 *
 * @author Ather Shu
 *        
 */
class Zipcode extends BaseValidator {

    public function validate($fieldName, $value) {
        if( $this->isEmpty( $value ) ) {
            return true;
        }
        else if( !preg_match( '/^\d{5}(-\d{4})?$/', $value ) ) {
            $this->composeRuleError( $fieldName );
        }
        return !$this->hasError();
    }

    protected function getDefaultErrorMessage($fieldName, $subRuleFlag = NULL, $threshold = NULL) {
        return "{$fieldName} must be valid zipcode.";
    }
}