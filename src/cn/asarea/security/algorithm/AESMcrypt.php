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
 * AES加密<br>
 * 1、有128、192、256三种位数，对应常量AESMcrypt::BIT_*<br>
 * 2、有ecb、cbc、cfb、ofb、nofb五种模式，对应常量MCRYPT_MODE_*，cbc模式是SSL、IPSec的标准模式。<br>
 * 参见http://www.cnblogs.com/happyhippy/archive/2006/12/23/601353.html<br>
 * 3、所有模式均需要密钥key，对于非ecb模式，还需要初始iv(Initialization Vector)。
 * 
 * @author Ather.Shu Nov 9, 2014 7:28:02 PM
 */
class AESMcrypt {

    const BIT_128 = 128;

    const BIT_192 = 192;

    const BIT_256 = 256;

    private $_cipher;

    private $_mode;

    private $_key;

    private $_iv;

    public function __construct($key, $mode = MCRYPT_MODE_ECB, $iv = NULL, $bit = AESMcrypt::BIT_128) {
        // check mcrypt module
        if( !extension_loaded( "mcrypt" ) ) {
            trigger_error( 'AESMcrypt class need mcrypt module' );
            return;
        }
        
        switch ($bit) {
            case AESMcrypt::BIT_192 :
                $this->_cipher = MCRYPT_RIJNDAEL_192;
                break;
            case AESMcrypt::BIT_256 :
                $this->_cipher = MCRYPT_RIJNDAEL_256;
                break;
            default :
                $this->_cipher = MCRYPT_RIJNDAEL_128;
                break;
        }
        
        $modes = array (
                MCRYPT_MODE_ECB,
                MCRYPT_MODE_CBC,
                MCRYPT_MODE_CFB,
                MCRYPT_MODE_OFB,
                MCRYPT_MODE_NOFB 
        );
        if( in_array( $mode, $modes ) ) {
            $this->_mode = $mode;
        }
        else {
            $this->_mode = MCRYPT_MODE_ECB;
        }
        
        $this->_key = $key;
        $this->_iv = $iv;
        // check iv size, ecb模式不需要iv
        if( $this->_mode != MCRYPT_MODE_ECB ) {
            $needIVSize = mcrypt_get_iv_size( $this->_cipher, $this->_mode );
            if( strlen( $iv ) != $needIVSize ) {
                trigger_error( "IV size wrong, need a string of length {$needIVSize}" );
                return;
            }
        }
    }

    /**
     * 加密
     * 
     * @param string $data 待加密字串
     * @return string
     */
    public function encrypt($data) {
        return mcrypt_encrypt( $this->_cipher, $this->_key, $data, $this->_mode, $this->_iv );
    }

    /**
     * 解密
     * 
     * @param string $data 待解密字串
     * @return string
     */
    public function decrypt($data) {
        // php手册：If the size of the data is not n * blocksize, the data will be padded with '\0'.
        $data = mcrypt_decrypt( $this->_cipher, $this->_key, $data, $this->_mode, $this->_iv );
        $data = rtrim( rtrim( $data ), "\x00..\x1F" );
        return $data;
    }
}