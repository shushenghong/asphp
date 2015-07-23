<?php
// ////////////////////////////////////////////////////////////////////////////
//
// ATHER.SHU WWW.ASAREA.CN
// All Rights Reserved.
// email: shushenghong@gmail.com
//
// ///////////////////////////////////////////////////////////////////////////
namespace cn\asarea\security\algorithm;
/**
 * Base32编码解码类<br>
 * 
 * @see http://xiaobin.net/201004/php-base32-encoding-and-decoding-according-rfc4648/
 * @author Ather.Shu Nov 9, 2014 7:29:47 PM
 */
class Base32 {

    private static $base32;

    private static function init() {
        if( !isset( Base32::$base32 ) ) {
            Base32::$base32 = new Base2n( 5, 'abcdefghijklmnopqrstuvwxyz234567', FALSE );
        }
    }

    /**
     * 编码
     * 
     * @param string $input
     * @return string
     */
    public static function encode($input) {
        Base32::init();
        return Base32::$base32->encode( $input );
    }

    /**
     * 解码
     * 
     * @param string $input
     * @return string
     */
    public static function decode($input) {
        Base32::init();
        return Base32::$base32->decode( $input );
    }
}