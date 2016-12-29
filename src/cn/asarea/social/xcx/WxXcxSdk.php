<?php
// ////////////////////////////////////////////////////////////////////////////
//
// Copyright (c) 2015-2016 Hangzhou Freewind Technology Co., Ltd.
// All rights reserved.
// http://www.seastart.cn
//
// ///////////////////////////////////////////////////////////////////////////
namespace cn\asarea\social\xcx;
use cn\asarea\social\BaseSocial;
/**
 * 微信小程序
 * @author Ather.Shu Dec 29, 2016 2:19:19 PM
 */
class WxXcxSdk extends BaseSocial {
    protected $appId;
    
    protected $appSecret;
    
    public function __construct($appId, $appSecret) {
        $this->appId = $appId;
        $this->appSecret = $appSecret;
    }
    
    /**
     * 获取微信session_key
     *
     * @return ['openid', 'session_key']
     */
    public function getSessionKey($code) {
        $response = $this->callAPI("https://api.weixin.qq.com/sns/jscode2session?appid={$this->appId}&secret={$this->appSecret}&js_code={$code}&grant_type=authorization_code");
        $response = json_decode( $response, true );
        var_export($response);
        if( isset( $response['errcode'] ) && $response['errcode'] ) {
            return FALSE;
        }
        else {
            return $response;
        }
    }
    
    /**
     * 解密数据
     * @param string $encryptedData
     * @param string $iv
     * @param string $sessionKey
     * @return string|boolean
     */
    public function decrptData($encryptedData, $iv, $sessionKey) {
        $pc = new WXBizDataCrypt($this->appId, $sessionKey);
        $errCode = $pc->decryptData($encryptedData, $iv, $data );
        return $data;
        
        if ($errCode == ErrorCode::$OK) {
            return $data;
        } else {
            return false;
        }
    }
}