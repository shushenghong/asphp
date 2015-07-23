<?php

namespace cn\asarea\validator;

/**
 * Required validator
 *
 * @author Ather Shu
 *        
 */
class Required extends BaseValidator {

    public function validate($fieldName, $value) {
        if( $this->isEmpty( $value ) ) {
            $this->composeRuleError( $fieldName );
            return false;
        }
        return true;
    }

    protected function getDefaultErrorMessage($fieldName, $subRuleFlag = NULL, $threshold = NULL) {
        $firstLetter = strtolower( substr( $fieldName, 0, 1 ) );
        switch ($firstLetter) {
            case 'a' :
            case 'e' :
            case 'i' :
            case 'o' :
            case 'u' :
                return "You must enter an {$fieldName}.";
            default :
                return "You must enter a {$fieldName}.";
        }
    }
}