<?php

namespace cn\asarea\validator;

/**
 * Decimal validator
 *
 * @author Ather Shu
 *        
 */
class Decimal extends Number {

    public function validate($fieldName, $value) {
        if( $this->isEmpty( $value ) ) {
            return true;
        }
        else if( !preg_match( '/^[-+]?\d*\.\d+$/', $value ) ) {
            $this->composeRuleError( $fieldName );
        }
        else if( parent::validate( $fieldName, $value ) ) {
            // additional params
            if( !empty( $this->params ) ) {
                $value = floatval( $value );
                foreach ( $this->params as $subRuleFlag => $threshold ) {
                    $threshold = $this->getSubRuleThreshold( $subRuleFlag );
                    switch ($subRuleFlag) {
                        case '.=' :
                            if( !preg_match( "/^[-+]?\d*\.\d{{$threshold}}$/", $value ) ) {
                                $this->composeRuleError( $fieldName, $subRuleFlag, $threshold );
                            }
                            break;
                        case '.>' :
                            $threshold++;
                        case '.>=' :
                            if( !preg_match( "/^[-+]?\d*\.\d{" . $threshold . ",}$/", $value ) ) {
                                $this->composeRuleError( $fieldName, $subRuleFlag, $threshold );
                            }
                            break;
                    }
                }
            }
        }
        return !$this->hasError();
    }

    protected function getDefaultErrorMessage($fieldName, $subRuleFlag = NULL, $threshold = NULL) {
        switch ($subRuleFlag) {
            case '.=' :
                return "{$fieldName} must have {$threshold} digits after the decimal point.";
            case '.>' :
            case '.>=' :
                return "{$fieldName} must have {$threshold} or more digits after the decimal point.";
            default :
                return empty( $subRuleFlag ) ? "{$fieldName} must be an decimal." : parent::getDefaultErrorMessage( $fieldName, $subRuleFlag, 
                        $threshold );
        }
    }
}