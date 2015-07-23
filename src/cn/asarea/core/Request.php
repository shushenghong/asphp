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
 * 请求类
 *
 * @author Ather.Shu Nov 9, 2014 10:13:12 PM
 */
class Request {

    const METHOD_GET = 0x0001;

    const METHOD_POST = 0x0010;

    /**
     * 默认get&post
     *
     * @var int
     */
    const METHOD_GETPOST = 0x0011;

    const METHOD_PUT = 0x0100;

    const METHOD_DELETE = 0x1000;

    /**
     * 获取当前请求的uri，如/item/12/
     *
     * @param $rmQParams bool 是否剔除get query param
     *       
     * @return string
     */
    public function getURI($rmQParams = true) {
        $rtn = rawurldecode( $_SERVER ['REQUEST_URI'] );
        if( $rmQParams && strpos( $rtn, '?' ) !== false ) {
            $rtn = strstr( $rtn, '?', true );
        }
        return $rtn;
    }

    /**
     * 获取当前请求method，对应METHOD_*
     *
     * @return int
     */
    public function getHttpMethod() {
        $method = isset( $_SERVER ['HTTP_X_HTTP_METHOD_OVERRIDE'] ) ? strtolower( $_SERVER ['HTTP_X_HTTP_METHOD_OVERRIDE'] ) : strtolower( 
                $_SERVER ['REQUEST_METHOD'] );
        switch ($method) {
            case 'get' :
                return self::METHOD_GET;
            case 'post' :
                return self::METHOD_POST;
            case 'put' :
                return self::METHOD_PUT;
            case 'delete' :
                return self::METHOD_DELETE;
        }
        return -1;
    }

    /**
     * 获取get参数
     *
     * @param string $param 参数名
     * @param mix $default 不存在时的默认值
     * @return string
     */
    public function fetchGet($param, $default = NULL) {
        if( isset( $_GET [$param] ) ) {
            return $_GET [$param];
        }
        return $default;
    }

    /**
     * 获取post参数
     *
     * @param string $param 参数名
     * @param mix $default 不存在时的默认值
     * @return string
     */
    public function fetchPost($param, $default = NULL) {
        if( isset( $_POST [$param] ) ) {
            return $_POST [$param];
        }
        return $default;
    }

    /**
     * 获取post过来的file
     *
     * @param string $param 文件字段参数名
     * @param string $default 不存在时的默认值
     * @return array [name => 'original file name', type => 'image/gif', tmp_name => 'temporary filename', error => 'error code', size='bytes']
     */
    public function fetchFile($param, $default = NULL) {
        if( isset( $_FILES [$param] ) ) {
            return $_FILES [$param];
        }
        return $default;
    }

    /**
     * 获取整体请求input
     *
     * @return string
     */
    public function fetchContent() {
        return file_get_contents( 'php://input' );
    }

    /**
     * 获取header
     *
     * @param string $param 参数名
     * @param mix $default 不存在时的默认值
     * @return string
     */
    public function fetchHeader($param, $default = NULL) {
        if( isset( $_SERVER [$param] ) ) {
            return $_SERVER [$param];
        }
        else if( isset( $_SERVER [strtoupper( "HTTP_{$param}" )] ) ) {
            return $_SERVER [strtoupper( "HTTP_{$param}" )];
        }
        return $default;
    }

    /**
     * 获取客户ip
     *
     * @return string
     */
    public function getClientIP() {
        return $_SERVER ['REMOTE_ADDR'];
    }

    /**
     * 是否是ajax请求
     *
     * @return boolean
     */
    public function isAjax() {
        return isset( $_SERVER ['X-Requested-With'] ) && $_SERVER ['X-Requested-With'] == 'XMLHttpRequest';
    }
}