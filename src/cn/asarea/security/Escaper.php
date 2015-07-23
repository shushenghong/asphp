<?php
// ////////////////////////////////////////////////////////////////////////////
//
// ATHER.SHU WWW.ASAREA.CN
// All Rights Reserved.
// email: shushenghong@gmail.com
//
// ///////////////////////////////////////////////////////////////////////////
namespace cn\asarea\security;
/**
 * escape外部输入参数
 * 
 * @author Ather.Shu Nov 9, 2014 8:33:40 PM
 */
class Escaper {

    /**
     * sql语句字串变量转义，防止sql注入
     * 
     * @param string $str
     * @param boolean $replaceTags 自动替换< >标签，防止xss攻击
     * @return string
     */
    public static function cs($str, $replaceTags = TRUE) {
        if( $replaceTags ) {
            $str = strtr( $str, array (
                    "<" => "&lt;",
                    ">" => "&gt;" 
            ) );
        }
        return addslashes( trim( $str ) );
    }

    /**
     * sql语句int变量转义，防止sql注入
     * 
     * @param int $i
     * @return int
     */
    public static function ci($i) {
        return intval( $i );
    }
}