<?php

namespace cn\asarea\validator;

/**
 * String validator
 *
 * @author Ather Shu
 *        
 */
class String extends BaseValidator {

    public function validate($fieldName, $value) {
        if( $this->isEmpty( $value ) ) {
            return true;
        }
        else if( !is_string( $value ) ) {
            $this->composeRuleError( $fieldName );
        }
        // additional params
        else if( !empty( $this->params ) ) {
            foreach ( $this->params as $subRuleFlag => $threshold ) {
                $threshold = $this->getSubRuleThreshold( $subRuleFlag );
                switch ($subRuleFlag) {
                    case 'maxLength' :
                        if( mb_strlen( $value, 'UTF-8' ) > $threshold ) {
                            $this->composeRuleError( $fieldName, $subRuleFlag, $threshold );
                        }
                        break;
                    case 'minLength' :
                        if( mb_strlen( $value, 'UTF-8' ) < $threshold ) {
                            $this->composeRuleError( $fieldName, $subRuleFlag, $threshold );
                        }
                        break;
                    case 'badwords' :
                        foreach ( $threshold as $word ) {
                            if( stripos( $value, $word ) !== false ) {
                                $this->composeRuleError( $fieldName, $subRuleFlag, $word );
                            }
                        }
                        break;
                }
            }
        }
        return !$this->hasError();
    }

    protected function getDefaultErrorMessage($fieldName, $subRuleFlag = NULL, $threshold = NULL) {
        switch ($subRuleFlag) {
            case 'maxLength' :
                return "{$fieldName} must be no more than {$threshold} characters.";
            case 'minLength' :
                return "{$fieldName} should be at least {$threshold} characters.";
            case 'badwords' :
                return "{$fieldName} can't container word {$threshold}.";
            default :
                return "{$fieldName} must be a string.";
        }
    }
}