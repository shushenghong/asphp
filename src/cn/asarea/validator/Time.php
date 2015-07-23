<?php

namespace cn\asarea\validator;

/**
 * Time validator
 *
 * @author Ather Shu
 *        
 */
class Time extends BaseValidator {

    public function validate($fieldName, $value) {
        if( $this->isEmpty( $value ) ) {
            return true;
        }
        else if( !preg_match( '/^([0-1]?\d|2[0-3]):([0-5]?\d)$/', $value ) ) {
            $this->composeRuleError( $fieldName );
        }
        // additional params
        else if( !empty( $this->params ) ) {
            $pt = floatval( str_replace( ":", ".", $value ) );
            foreach ( $this->params as $subRuleFlag => $threshold ) {
                $threshold = $this->getSubRuleThreshold( $subRuleFlag );
                switch ($subRuleFlag) {
                    case '>' :
                        if( $threshold == 'now' ) {
                            $threshold = date( "H:i" );
                        }
                        if( $value <= floatval( str_replace( ":", ".", $threshold ) ) ) {
                            $this->composeRuleError( $fieldName, $subRuleFlag, $threshold );
                        }
                        break;
                    case '<' :
                        if( $threshold == 'now' ) {
                            $threshold = date( "H:i" );
                        }
                        if( $value >= floatval( str_replace( ":", ".", $threshold ) ) ) {
                            $this->composeRuleError( $fieldName, $subRuleFlag, $threshold );
                        }
                        break;
                }
            }
        }
        return !$this->hasError();
    }

    protected function getDefaultErrorMessage($fieldName, $subRuleFlag = NULL, $threshold = NULL) {
        switch ($subRuleFlag) {
            case '>' :
                return "{$fieldName} must after {$threshold}.";
            case '<' :
                return "{$fieldName} must before {$threshold}.";
            default :
                return "{$fieldName} must be valid time.";
        }
    }
}