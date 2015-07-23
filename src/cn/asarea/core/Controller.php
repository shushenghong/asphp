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
 * 控制器<br>
 * 各action方法名称如actionIndex actionView
 * 
 * @author Ather.Shu Nov 9, 2014 10:14:18 PM
 */
class Controller {

    /**
     * run某action
     * 
     * @param string $action
     * @param array $params
     */
    public function runAction($action, $params) {
        $methodName = 'action' . ucfirst( $action );
        if( method_exists( $this, $methodName ) ) {
            if( $this->beforeAction( $action, $params ) ) {
                call_user_func_array( array (
                        $this,
                        $methodName 
                ), $params );
            }
        }
    }

    /**
     * 在run某action前可以做权限检查，数据修改等
     * 
     * @param string $action
     * @param array $params
     * @return boolean false代表检查不通过，不能继续run对应action
     */
    protected function beforeAction($action, $params) {
        return true;
    }
}