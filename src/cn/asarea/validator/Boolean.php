<?php

namespace cn\asarea\validator;

/**
 * boolean validator
 *
 * @author Ather Shu
 *        
 */
class Boolean extends BaseValidator {

    public function validate($fieldName, $value) {
        if( $this->isEmpty( $value ) ) {
            return true;
        }
        else if( !preg_match( '/^(0|1|true|false)$/i', $value ) ) {
            $this->composeRuleError( $fieldName );
        }
        return !$this->hasError();
    }

    protected function getDefaultErrorMessage($fieldName, $subRuleFlag = NULL, $threshold = NULL) {
        return "{$fieldName} must be a bool.";
    }
}