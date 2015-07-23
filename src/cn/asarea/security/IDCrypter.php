<?php
// ////////////////////////////////////////////////////////////////////////////
//
// ATHER.SHU WWW.ASAREA.CN
// All Rights Reserved.
// email: shushenghong@gmail.com
//
// ///////////////////////////////////////////////////////////////////////////
namespace cn\asarea\security;
use cn\asarea\security\algorithm\AESMcrypt;
/**
 * id加密器，采用模式为CBC的128位的AES加密<br>
 * 输出格式如：d8ddab483c654b79853117721213bb9c
 * 
 * @author Ather.Shu Nov 9, 2014 7:27:24 PM
 */
class IDCrypter {

    /**
     * 加密
     * 
     * @param int $id 原始id
     * @param string $key 密钥
     * @param string $iv 可以想象为必须的另一个密钥，长度必须为16
     */
    public static function encrypt($id, $key, $iv) {
        $aes = new AESMcrypt( $key, MCRYPT_MODE_CBC, $iv, AESMcrypt::BIT_128 );
        // return Base32::encode( $aes->encrypt($id) );
        return bin2hex( $aes->encrypt( $id ) );
    }

    /**
     * 解密
     * 
     * @param int $encID 加密过的id
     * @param string $key 密钥
     * @param string $iv 可以想象为必须的另一个密钥，长度必须为16
     */
    public static function decrypt($encID, $key, $iv) {
        $aes = new AESMcrypt( $key, MCRYPT_MODE_CBC, $iv, AESMcrypt::BIT_128 );
        // return $aes->decrypt( Base32::decode($encID) );
        return $aes->decrypt( @hex2bin( $encID ) );
    }
}


