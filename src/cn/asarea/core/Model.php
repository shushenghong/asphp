<?php
// ////////////////////////////////////////////////////////////////////////////
//
// ATHER.SHU WWW.ASAREA.CN
// All Rights Reserved.
// email: shushenghong@gmail.com
//
// ///////////////////////////////////////////////////////////////////////////
namespace cn\asarea\core;
/**
 * 数据模型
 * @author Ather.Shu Nov 9, 2014 10:14:10 PM
 */
abstract class Model {
    /**
     * model scene create
     */
    const SCENE_CREATE = 1;
    
    /**
     * model scene update
     */
    const SCENE_UPDATE = 2;
    
    /**
     * current scene
     */
    protected $_scene;
    
    /**
     * raw input values dict (field => value), only for validate use
     */
    protected $_rawInputValues;
    
    public function __construct($scene = Constants::SCENE_CREATE) {
        $this->_rawInputValues = array ();
        $this->_scene = $scene;
    }
    
    public function __get($name) {
    
        // 1. check getter exist
        $getters = $this->getterSetters( 'getters' );
        if( isset( $getters [$name] ) ) {
            $method = $getters [$name];
            return $this->$method();
        }
        // 2. check record field
        if( !empty( $this->_record ) ) {
            $fields = $this->_record->getColumns();
            if( array_key_exists( $name, $fields ) ) {
                return $this->_record->$name;
            }
        }
    
        return null;
    }
    
    public function __set($name, $value) {
        $this->signRawInput( $name, $value );
        // 1. check setter exist
        $setters = $this->getterSetters( 'setters' );
        if( isset( $setters [$name] ) ) {
            $method = $setters [$name];
            return $this->$method( $value );
        }
        // 2. check record field
        if( !empty( $this->_record ) ) {
            $fields = $this->_record->getColumns();
            if( array_key_exists( $name, $fields ) ) {
                return $this->_record->$name = $value;
            }
        }
    }
    
    /**
     * backup raw input, for validation later<br>
     * each customized setter should call this method
     */
    protected function signRawInput($fieldOrSetter, $value) {
        // normalize field name
        $field = $fieldOrSetter;
        $setters = $this->getterSetters( 'setters' );
        foreach ( $setters as $tmpField => $setter ) {
            if( $setter == $fieldOrSetter ) {
                $field = $tmpField;
                break;
            }
        }
        // backup
        $this->_rawInputValues [$field] = $value;
    }
    
    /**
     * get raw input for validation
     */
    private function getRawInput($field) {
        if( array_key_exists( $field, $this->_rawInputValues ) ) {
            return $this->_rawInputValues [$field];
        }
        return null;
    }
    
    /**
     * check field have input
     * @param string $field
     */
    protected function hasRawInput($field) {
        return array_key_exists( $field, $this->_rawInputValues );
    }
    
    /**
     * inner get getters and setter
     */
    private function getterSetters($type = 'getters') {
        $reflectionCls = new \ReflectionClass( $this );
        $methods = $reflectionCls->getMethods( \ReflectionMethod::IS_PUBLIC );
        $rtn = array (
                'getters' => array (),
                'setters' => array ()
        );
        /* @var $method \ReflectionMethod */
        foreach ( $methods as $method ) {
            $methodName = $method->getName();
            if( $method->isStatic() || $methodName == 'getSerializeData' || $methodName == 'getErrors' || $methodName == 'getClsName' ) {
                continue;
            }
            $start = substr( $methodName, 0, 3 );
            if( $start === 'get' || $start === 'set' ) {
                $name = lcfirst( substr( $methodName, 3 ) );
                $field = strtolower( preg_replace( "/([A-Z])/", "_$1", $name ) );
                $rtn [$start == 'get' ? 'getters' : 'setters'] [$field] = $methodName;
            }
        }
        return $rtn [$type];
    }
    
    /**
     * batch set model attributes, no validate, we will validate later such as when save to db
     *
     * @param object $input
     */
    public final function attrs($input) {
        $scene = $this->_scene;
        $rules = $this->rules();
        if( empty( $rules ) ) {
            return;
        }
        foreach ( $rules as $field => $rule ) {
            // check scene
            if( isset( $rule ['scene'] ) && $rule ['scene'] != $scene ) {
                continue;
            }
            unset( $rule ['scene'] );
            // field name
            $fieldName = $this->getSerializeFieldName( $field, true );
            // if is scene update, only modify setted fields
            if( $scene == Constants::SCENE_UPDATE && !array_key_exists( $fieldName, $input ) ) {
                continue;
            }
    
            $value = isset( $input [$fieldName] ) ? $input [$fieldName] : NULL;
            $this->$field = $value;
        }
    }
    
    /**
     * validate user input fields before operate database for different scenarios
     */
    public final function validate() {
        $scene = $this->_scene;
        $rules = $this->rules();
        if( empty( $rules ) ) {
            return true;
        }
        foreach ( $rules as $field => $rule ) {
            // check scene
            if( isset( $rule ['scene'] ) && $rule ['scene'] != $scene ) {
                continue;
            }
            unset( $rule ['scene'] );
            // get label or msg
            if( isset( $rule ['label'] ) ) {
                $label = $rule ['label'];
                unset( $rule ['label'] );
            }
            else {
                $label = null;
            }
            if( isset( $rule ['msg'] ) ) {
                $msg = $rule ['msg'];
                unset( $rule ['msg'] );
            }
            else {
                $msg = null;
            }
            // field name
            $fieldName = $this->getSerializeFieldName( $field, true );
            // use raw input instead of getter! getter is for output serialize, inner validate should use original input
            $value = $this->getRawInput( $field );
            // if is scene update and the field is not modified, no need validate this field
            if( $scene == Constants::SCENE_UPDATE && !array_key_exists( $field, $this->_rawInputValues ) ) {
                continue;
            }
            // check validators
            foreach ( $rule as $validator => $validParams ) {
                if( is_int( $validator ) ) {
                    $validator = $validParams;
                    $validParams = null;
                }
                $reflectionCls = new \ReflectionClass( "\\cn\\asarea\\validator\\" . ucfirst( $validator ) );
                /* @var $validator \Community\Validator\BaseValidator */
                $validator = $reflectionCls->newInstance( $validParams );
    
                if( !$validator->validate( isset( $label ) ? $label : $fieldName, $value ) ) {
                    $errors = $validator->getErrors();
                    foreach ( $errors as $error ) {
                        $this->addError( isset( $msg ) ? $msg : $error ['msg'], $error ['code'], $fieldName );
                    }
                }
            }
        }
        // customize validate
        $this->customizeValidate();
    
        return !$this->hasError();
    }
    
    /**
     * get data for serialize
     *
     * @return array
     */
    public final function getSerializeData() {
        $rtn = array ();
        // 1. check getters
        $getters = $this->getterSetters( 'getters' );
        // 2. check record fields
        $recordFields = empty( $this->_record ) ? array () : $this->_record->getColumns();
        // merge
        $fields = array_merge( array_keys( $getters ), array_keys( $recordFields ) );
        array_unique( $fields );
        //         Utils::log( $fields );
        foreach ( $fields as $field ) {
            $fieldName = $this->getSerializeFieldName( $field );
            if( !empty( $fieldName ) ) {
                $rtn [$fieldName] = $this->$field;
                if( $rtn [$fieldName] instanceof \Community\Model ) {
                    $rtn [$fieldName] = $rtn [$fieldName]->getSerializeData();
                }
            }
        }
        return $rtn;
    }
    
    /**
     * get serialized field name by the record field name <br>
     * basically: food_id will transfer to foodId
     *
     * @param string $recordFieldName
     * @param string $forDeserialize if it's for deserialize and have specialFieldMapping to null(means field have no output, just input), return normal camecase transfel instead of null.
     */
    protected final function getSerializeFieldName($recordFieldName, $forDeserialize = false) {
        $specialMapping = $this->specialFieldMapping();
        if( !empty( $specialMapping ) && array_key_exists( $recordFieldName, $specialMapping ) ) {
            if( !$forDeserialize || !empty( $specialMapping [$recordFieldName] ) ) {
                return $specialMapping [$recordFieldName];
            }
        }
        return lcfirst( str_replace( ' ', '', ucwords( str_replace( '_', ' ', $recordFieldName ) ) ) );
    }
    
    /**
     * get last static binding class name
     *
     * @return string
     */
    public static function getClsName() {
        return get_called_class();
    }
    
    /**
     * check rules after deserialize, each field that able changed by user must defined here
     */
    public function rules() {
        return null;
    }
    
    /**
     * special fields mapping for serialize <br>
     * only need define the fields will not serialize or <br>
     * not use camelCase translate record_id => recordId <br>
     * such as record_id => myID (record_id will output as myID instead of recordId)<br>
     * record_id => null (record_id will not serialize)
     *
     * @return array record field => output field
     */
    public function specialFieldMapping() {
        return null;
    }
    
    /**
     * Override this to customize validate after the auto validate
     */
    public function customizeValidate() {
    }
}