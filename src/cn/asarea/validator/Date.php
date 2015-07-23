<?php

namespace cn\asarea\validator;

/**
 * Date validator
 *
 * @author Ather Shu
 *        
 */
class Date extends BaseValidator {

    public function validate($fieldName, $value) {
        if( $this->isEmpty( $value ) ) {
            return true;
        }
        $format = 'Y-m-d';
        if( !empty( $this->params ) && isset( $this->params ['format'] ) ) {
            $format = $this->params ['format'];
        }
        $date = \DateTime::createFromFormat( $format, $value );
        if( $date === false ) {
            $this->composeRuleError( $fieldName );
        }
        // additional params
        else if( !empty( $this->params ) ) {
            foreach ( $this->params as $subRuleFlag => $threshold ) {
                $threshold = $this->getSubRuleThreshold( $subRuleFlag );
                switch ($subRuleFlag) {
                    case '>' :
                        if( strtotime( $value ) <= strtotime( $threshold ) ) {
                            $threshold = date( $format, strtotime( $threshold ) );
                            $this->composeRuleError( $fieldName, $subRuleFlag, $threshold );
                        }
                        break;
                    case '<' :
                        if( strtotime( $value ) >= strtotime( $threshold ) ) {
                            $threshold = date( $format, strtotime( $threshold ) );
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
                return "{$fieldName} must be valid date.";
        }
    }
}