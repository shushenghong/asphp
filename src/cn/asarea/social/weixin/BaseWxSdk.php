<?php
// ////////////////////////////////////////////////////////////////////////////
//
// ATHER.SHU WWW.ASAREA.CN
// All Rights Reserved.
// email: shushenghong@gmail.com
//
// ///////////////////////////////////////////////////////////////////////////
namespace cn\asarea\social\weixin;

use cn\asarea\social\BaseSocial;

/**
 * 微信开放平台基础sdk类
 *
 * @author Ather.Shu Feb 9, 2015 3:31:59 PM
 */
class BaseWxSdk extends BaseSocial {

    protected $appId;

    protected $appSecret;

    protected $ticketSavePath;

    public function __construct($appId, $appSecret, $ticketSavePath) {
        $this->appId = $appId;
        $this->appSecret = $appSecret;
        $this->ticketSavePath = $ticketSavePath;
    }

    /**
     * 获取微信access token
     *
     * @return string
     */
    protected function getAccessToken() {
        // access_token 应该全局存储与更新，以下代码以写入到文件中做示例
        $tokenFile = "{$this->ticketSavePath}/wx_access_token.json";
        $data = file_exists( $tokenFile ) ? json_decode( file_get_contents( $tokenFile ) ) : null;
        if( empty( $data ) || $data->expire_time < time() ) {
            // 如果是企业号用以下URL获取access_token
            // $url = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=$this->appId&corpsecret=$this->appSecret";
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$this->appId&secret=$this->appSecret";
            $res = $this->callAPI( $url );
            if( $res ) {
                $data = new \stdClass();
                $data->expire_time = time() + $res->expires_in;
                $data->access_token = $res->access_token;
                file_put_contents( $tokenFile, json_encode( $data ) );
            }
        }
        $access_token = $data->access_token;
        return $access_token;
    }

    protected function callAPI($url, $queryParams = NULL, $method = "GET") {
        $response = parent::callAPI( $url, $queryParams, $method );
        // "errcode":40013,"errmsg":"invalid appid
        $response = json_decode( $response );
        if( isset( $response->errcode ) && $response->errcode ) {
            return FALSE;
        }
        else {
            return $response;
        }
    }
}