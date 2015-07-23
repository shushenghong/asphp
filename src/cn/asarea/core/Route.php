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
 * 路由规则
 *
 * @author Ather.Shu Nov 9, 2014 10:21:48 PM
 */
class Route {

    /**
     * route http method
     *
     * @var string
     */
    private $_httpMethod;

    /**
     * route 正则
     *
     * @var string
     */
    private $_regex;

    /**
     * 对应controller
     *
     * @var string
     */
    private $_controller;

    /**
     * 对应action
     *
     * @var string
     */
    private $_action;

    /**
     * 所有路由
     *
     * @var array
     */
    private static $_routes = array ();

    /**
     * 注册一条route
     *
     * @param string $regex
     * @param string $controller
     * @param string $action
     * @param int $httpMethod
     */
    public static function register($regex, $controller, $action = 'index', $httpMethod = Request::METHOD_GETPOST) {
        // 自动在正则头部加上^
        if( substr( $regex, 0, 1 ) != '^' ) {
            $regex = '^' . $regex;
        }
        // 自动在正则末尾加上/，http://127.0.0.7/xx应该与http://127.0.0.1/xx/
        if( substr( $regex, -1 ) != '$' && substr( $regex, -2 ) != '/?' ) {
            $regex .= (substr( $regex, -1 ) == '/' ? '?$' : '/?$');
        }
        if( substr( $regex, -1 ) != '$' ) {
            $regex .= '$';
        }
        // 拼接成php认识的正则表达式
        $regex = '/' . str_replace( '/', '\/', $regex ) . '/';
        $route = new self( $regex, $controller, $action, $httpMethod );
        array_push( self::$_routes, $route );
    }

    /**
     * 检查所有route，match并且run对应controller
     *
     * @param $request Request
     */
    public static function checkMatchAndRun($request) {
        $matches = array ();
        $uri = $request->getURI();
        $httpMethod = $request->getHttpMethod();
        /* @var $route Route */
//         var_dump( $uri, $httpMethod );
        foreach ( self::$_routes as $route ) {
//             var_dump( $route->_httpMethod, $route->_regex );
            // method以及正则都吻合
            if( ($httpMethod & $route->_httpMethod) == $httpMethod && preg_match( $route->_regex, $uri, $matches ) ) {
                array_shift( $matches );
                $controller = new $route->_controller();
                call_user_func( array (
                    $controller,
                    'runAction' 
                ), $route->_action, $matches );
                break;
            }
        }
    }

    public function __construct($regex, $controller, $action, $httpMethod) {
        $this->_regex = $regex;
        $this->_controller = $controller;
        $this->_action = $action;
        $this->_httpMethod = $httpMethod;
    }
}