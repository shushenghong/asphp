<?php

namespace cn\asarea\validator;

/**
 * Datetime validator
 *
 * @author Ather Shu
 *        
 */
class Datetime extends Date {

    public function validate($fieldName, $value) {
        if( $this->isEmpty( $value ) ) {
            return true;
        }
        
        if( empty( $this->params ) ) {
            $this->params = array ();
        }
        if( !isset( $this->params ['format'] ) ) {
            $this->params ['format'] = 'Y-m-d H:i:s';
        }
        return parent::validate( $fieldName, $value );
    }
}