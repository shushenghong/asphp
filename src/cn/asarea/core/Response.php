<?php
// ////////////////////////////////////////////////////////////////////////////
//
// ATHER.SHU WWW.ASAREA.CN
// All Rights Reserved.
// email: shushenghong@gmail.com
//
// ///////////////////////////////////////////////////////////////////////////
namespace cn\asarea\core;

use cn\asarea\net\Http;

/**
 * 响应
 *
 * @author Ather.Shu Nov 9, 2014 10:13:21 PM
 */
class Response {

    /**
     * 重新跳转
     *
     * @param $code int 跳转码，默认302临时跳转，301永久跳转
     */
    public function redirect($toUrl, $code = 302) {
        Http::headerRedirect( $toUrl, $code );
    }

    public function writeCookie($name, $value, $expire = 0, $path = null, $domain = null, $secure = null, $httponly = null) {
        setcookie( $name, $value, $expire, $path, $domain, $secure, $httponly );
    }

    public function writeJson($data) {
        header( 'X-Powered-By: asphp' );
        header( 'Content-Type: application/json; charset=utf-8' );
        echo json_encode( $data );
    }

    public function writeHtml($html) {
        header( 'X-Powered-By: asphp' );
        header( 'Content-Type: text/html; charset=utf-8' );
        echo $html;
    }

    public function writeXml($xml) {
        header( 'X-Powered-By: asphp' );
        header( 'Content-Type: text/xml; charset=utf-8' );
        echo $xml;
    }
}