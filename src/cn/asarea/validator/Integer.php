<?php

namespace cn\asarea\validator;

/**
 * Integar validator
 *
 * @author Ather Shu
 *        
 */
class Integer extends Number {

    public function validate($fieldName, $value) {
        if( $this->isEmpty( $value ) ) {
            return true;
        }
        else if( !preg_match( '/^[-]?\d+$/', $value ) ) {
            $this->composeRuleError( $fieldName );
        }
        else {
            parent::validate( $fieldName, $value );
        }
        return !$this->hasError();
    }

    protected function getDefaultErrorMessage($fieldName, $subRuleFlag = NULL, $threshold = NULL) {
        return empty( $subRuleFlag ) ? "{$fieldName} must be a whole number." : parent::getDefaultErrorMessage( $fieldName, $subRuleFlag, $threshold );
    }
}