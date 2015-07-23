<?php

namespace cn\asarea\validator;

/**
 * Number validator
 *
 * @author Ather Shu
 *        
 */
class Number extends BaseValidator {

    public function validate($fieldName, $value) {
        if( $this->isEmpty( $value ) ) {
            return true;
        }
        else if( !preg_match( '/^[-+]?\d*(\.\d*)*$/', $value ) ) {
            $this->composeRuleError($fieldName);
        }
        // additional params
        else if( !empty( $this->params ) ) {
            $value = floatval( $value );
            foreach ( $this->params as $subRuleFlag => $threshold ) {
                $threshold = $this->getSubRuleThreshold( $subRuleFlag );
                switch ($subRuleFlag) {
                    case '>' :
                        if( $value <= $threshold ) {
                            $this->composeRuleError($fieldName, $subRuleFlag, $threshold);
                        }
                        break;
                    case '>=' :
                        if( $value < $threshold ) {
                            $this->composeRuleError($fieldName, $subRuleFlag, $threshold);
                        }
                        break;
                    case '<' :
                        if( $value >= $threshold ) {
                            $this->composeRuleError($fieldName, $subRuleFlag, $threshold);
                        }
                        break;
                    case '<=' :
                        if( $value > $threshold ) {
                            $this->composeRuleError($fieldName, $subRuleFlag, $threshold);
                        }
                        break;
                    case '.<' :
                        $threshold--;
                    case '.<=' :
                        if( !preg_match( '/^[-+]?\d*$/', $value ) && !preg_match( "/^[-+]?\d*\.\d{1," . $threshold . "}$/", $value ) ) {
                            $this->composeRuleError($fieldName, $subRuleFlag, $threshold);
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
                return "{$fieldName} must be greater than {$threshold}.";
            case '>=' :
                return "{$fieldName} must be not smaller than {$threshold}.";
            case '<' :
                return "{$fieldName} must be smaller than {$threshold}.";
            case '<=' :
                return "{$fieldName} cannot be greater than {$threshold}.";
            case '.<' :
            case '.<=' :
                switch ($threshold) {
                    case 1 :
                        return "Round to the nearest tenth.";
                    case 2 :
                        return "Round to the nearest hundredth.";
                    case 3 :
                        return "Round to the nearest thousandth.";
                }
            default :
                return "{$fieldName} must be a number.";
        }
    }
}