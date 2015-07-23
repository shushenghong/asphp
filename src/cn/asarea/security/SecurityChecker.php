<?php
// ////////////////////////////////////////////////////////////////////////////
//
// ATHER.SHU WWW.ASAREA.CN
// All Rights Reserved.
// email: shushenghong@gmail.com
//
// ///////////////////////////////////////////////////////////////////////////
namespace cn\asarea\security;

use cn\asarea\core\Application;

/**
 * 安全检测相关类
 *
 * @author Ather.Shu Nov 9, 2014 7:21:44 PM
 */
class SecurityChecker {

    /**
     * 获取security某子段config
     */
    public static function getSecurityConfig($section) {
        $securityConfig = Application::getInstance()->getConfig( 'security' );
        return $securityConfig [$section];
    }

    /**
     * 检查是否盗链，主要用于ajax
     *
     * @param string $specialRefer 特定refer，正则表达式字串，如"/baidu\.com/i",默认为空，为空时会检测app config中的allowdomain
     * @return boolean 是否来自允许的domain
     */
    public static function checkRefer($specialRefer = NULL) {
        if( !isset( $_SERVER ['HTTP_REFERER'] ) ) {
            return FALSE;
        }
        else if( !empty( $specialRefer ) ) {
            return preg_match( $specialRefer, $_SERVER ['HTTP_REFERER'] ) == 1;
        }
        $refer = parse_url( $_SERVER ['HTTP_REFERER'], PHP_URL_HOST );
        $allowDomains = explode( ',', self::getSecurityConfig( 'allow_refer' ) );
        foreach ( $allowDomains as $domain ) {
            if( stripos( $refer, trim( $domain ) ) !== FALSE ) {
                return TRUE;
            }
        }
        return FALSE;
    }

    /**
     * 检查是否有useragent，防止脚本抓取或者检测特定agent
     *
     * @param string $specialAgent 特定agent，正则表达式字串，如"/msie|webkit/i",默认为空
     * @return boolean 是否有useragent，false代表没有useragent
     */
    public static function checkUserAgent($specialAgent = NULL) {
        if( !isset( $_SERVER ['HTTP_USER_AGENT'] ) ) {
            return FALSE;
        }
        else if( !empty( $specialAgent ) && preg_match( $specialAgent, $_SERVER ['HTTP_USER_AGENT'] ) == 0 ) {
            return FALSE;
        }
        return TRUE;
    }

    /**
     * 以严格的模式检测是否浏览器，这会屏蔽非法抓取以及机器人等，一般仅用在重要的接口中
     * 原理：
     * 经调试，一般浏览器都会至少发送以下几项：
     * User-Agent:Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/29.0.1547.76 Safari/537.36
     * Connection: keep-alive
     * Accept-Encoding: gzip,deflate,sdch
     * Accept-Language: zh-CN,zh;q=0.8
     * 而curl等需要人工加上上述头
     */
    public static function checkIsBrowser() {
        if( !isset( $_SERVER ['HTTP_USER_AGENT'] ) || !isset( $_SERVER ['HTTP_CONNECTION'] ) || !isset( $_SERVER ['HTTP_ACCEPT_ENCODING'] ) ||
                 !isset( $_SERVER ['HTTP_ACCEPT_LANGUAGE'] ) ) {
            return FALSE;
        }
        return TRUE;
    }

    /**
     * 检查是否是jquery调用产生的ajax请求，默认$.ajax都会产生一个X-Requested-With:XMLHttpRequest的请求头做区分
     *
     * @return boolean 是否是ajax请求
     */
    public static function checkIsAjax() {
        return isset( $_SERVER ['HTTP_X_REQUESTED_WITH'] ) && $_SERVER ['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }

    /**
     * 是否已登录
     *
     * @param $sessionParam string session中验证是否登录的字段，null则从配置中读取
     * @return boolean 是否已经登陆
     */
    public static function checkLogin($sessionParam = NULL) {
        if( session_id() == '' ) {
            session_start();
        }
        return isset( $_SESSION [isset( $sessionParam ) ? $sessionParam : self::getSecurityConfig( 'session_check_login' )] );
    }

    /**
     * 检查验证码是否正确
     *
     * @param string $vcode 输入验证码
     * @param string $sessionParam session中验证码字段名称
     * @return boolean 是否验证通过
     */
    public static function checkVerifyCode($vcode, $sessionParam = "code") {
        if( session_id() == '' ) {
            session_start();
        }
        $flag = !empty( $vcode ) && isset( $_SESSION [$sessionParam] ) && $vcode == $_SESSION [$sessionParam];
        // 一个验证码只能使用一次，验证通过后，清空后台验证码，由前台主动刷新一次验证码
        unset( $_SESSION [$sessionParam] );
        return $flag;
    }

    /**
     * 检查ip是否不在黑名单
     *
     * @return boolean false代表ip被禁用
     */
    public static function checkIP() {
        $ip = $_SERVER ["REMOTE_ADDR"];
        $blocks = explode( ',', self::getSecurityConfig( 'block_ips' ) );
        foreach ( $blocks as $block ) {
            if( strcasecmp( $ip, trim( $block ) ) == 0 ) {
                return FALSE;
            }
        }
        return TRUE;
    }

    /**
     * 检查是否为mobile浏览
     *
     * @return boolean true代表为手机浏览
     */
    public static function checkMobile() {
        $useragent = $_SERVER ['HTTP_USER_AGENT'];
        if( preg_match( 
                '/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i', 
                $useragent ) || preg_match( 
                '/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i', 
                substr( $useragent, 0, 4 ) ) ) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * 关键数据授权验证
     *
     * @deprecated 暂时没啥用，因为抓取软件都不会产生session的
     */
    public static function checkAuthToken() {
        if( session_id() == '' ) {
            session_start();
        }
        // cookie取token
        $clientToken = isset( $_COOKIE [AUTH_TOKEN_NAME] ) ? $_COOKIE [AUTH_TOKEN_NAME] : '';
        $serverToken = isset( $_SESSION [AUTH_TOKEN_NAME] ) ? $_SESSION [AUTH_TOKEN_NAME] : '';
        $flag = ($clientToken == $serverToken);
        // 不匹配次数更新
        if( !$flag ) {
            $unmatchTimes = isset( $_SESSION ['authMissTimes'] ) ? $_SESSION ['authMissTimes'] : 0;
            $unmatchTimes++;
            // 最大不匹配次数要考虑多开同时发送请求的影响
            if( $unmatchTimes <= AUTH_MISS_MAX_TIMES ) {
                $flag = true;
            }
            $_SESSION ['authMissTimes'] = $unmatchTimes;
        }
        else {
            unset( $_SESSION ['authMissTimes'] );
        }
        // 更新token
        $token = md5( mt_rand() . AUTH_TOKEN_NAME );
        setcookie( AUTH_TOKEN_NAME, $token, 0, "/", null, null, true );
        $_SESSION [AUTH_TOKEN_NAME] = $token;
        
        return $flag;
    }
}