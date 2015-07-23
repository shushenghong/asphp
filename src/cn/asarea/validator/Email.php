<?php

namespace cn\asarea\validator;

/**
 * Email validator
 *
 * @author Ather Shu
 *        
 */
class Email extends String {

    public function validate($fieldName, $value) {
        if( $this->isEmpty( $value ) ) {
            return true;
        }
        else if( !preg_match( '/^[\w\.]+@[\w]+\.[\w]{1,3}$/', $value ) ) {
            $this->composeRuleError( $fieldName );
        }
        else {
            parent::validate( $fieldName, $value );
        }
        return !$this->hasError();
    }

    protected function getDefaultErrorMessage($fieldName, $subRuleFlag = NULL, $threshold = NULL) {
        return "{$fieldName} must be valid email address.";
    }
}