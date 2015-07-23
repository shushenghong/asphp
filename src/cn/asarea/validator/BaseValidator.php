<?php

namespace cn\asarea\validator;

/**
 * base validator for user input params before save
 *
 * @author Ather Shu
 *        
 */
class BaseValidator {
    use \Community\Mixin\ErrorContainer;

    /**
     * additional params
     *
     * @var array
     */
    protected $params;

    public function __construct($params = null) {
        $this->params = $params;
    }

    /**
     * validate a field with a value
     *
     * @param string $fieldName
     * @param mixed $value
     * @return boolean
     */
    public function validate($fieldName, $value) {
        return true;
    }

    /**
     * check the value is not set or is an empty string
     *
     * @param mixed $value
     */
    protected function isEmpty($value) {
        return !isset( $value ) || $value === '';
    }

    /**
     * add an field error due to a special sub rule
     *
     * @param string $fieldName
     * @param string $subRuleFlag <, >, >=
     * @param multitype: $threshold sub rule threshold value
     */
    protected final function composeRuleError($fieldName, $subRuleFlag = NULL, $threshold = NULL) {
        if( empty( $subRuleFlag ) ) {
            if( is_string( $this->params ) ) {
                $msg = $this->params;
            }
            else if( is_array( $this->params ) && isset( $this->params ['msg'] ) ) {
                $msg = $this->params ['msg'];
            }
        }
        else {
            if( isset( $this->params ) && isset( $this->params [$subRuleFlag] ) ) {
                $ruleParams = $this->params [$subRuleFlag];
                if( is_array( $ruleParams ) && isset( $ruleParams ['msg'] ) ) {
                    $msg = $ruleParams ['msg'];
                }
            }
        }
        if( !isset( $msg ) ) {
            $msg = $this->getDefaultErrorMessage( $fieldName, $subRuleFlag, $threshold );
        }
        if( !empty( $msg ) ) {
            $this->addError( $msg );
        }
    }

    /**
     * get default error message for a fieldname (with special sub rule, such as <, >=)
     *
     * this method shuld override by sub validator class
     *
     * @param string $fieldName
     * @param string $subRuleFlag <, >, >=
     * @param multitype: $threshold sub rule threshold value
     * @return string
     */
    protected function getDefaultErrorMessage($fieldName, $subRuleFlag = NULL, $threshold = NULL) {
        return '';
    }

    /**
     * get the threshold value for a special rule
     *
     * @param string $subRuleFlag <, >, >=
     * @return Ambigous <>|multitype:
     */
    protected function getSubRuleThreshold($subRuleFlag) {
        if( empty( $subRuleFlag ) ) {
            return null;
        }
        if( isset( $this->params ) && isset( $this->params [$subRuleFlag] ) ) {
            $ruleParams = $this->params [$subRuleFlag];
            if( is_array( $ruleParams ) && isset( $ruleParams ['val'] ) ) {
                return $ruleParams ['val'];
            }
            else {
                return $ruleParams;
            }
        }
    }
}