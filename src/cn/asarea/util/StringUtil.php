<?php
// ////////////////////////////////////////////////////////////////////////////
//
// ATHER.SHU WWW.ASAREA.CN
// All Rights Reserved.
// email: shushenghong@gmail.com
//
// ///////////////////////////////////////////////////////////////////////////
namespace cn\asarea\util;
/**
 * 字符串辅助类
 * 
 * @author Ather.Shu Nov 9, 2014 7:58:01 PM
 */
class StringUtil {
    // 文字到拼音映射数组
    private static $PIN_YIN_DICT = array ();
    // 多音字数据文件是由两个汉字组成的词语到拼音的映射
    private static $DYZ_DICT = array ();

    /**
     * 返回字串的utf8长度，多字节字符当1个字符
     * 
     * @param string $str
     * @return number 长度
     */
    public static function utf8len($str) {
        return mb_strlen( $str, "UTF-8" );
    }

    /**
     * 字符串截取，超过长度的截断，并在末尾加上 '...'
     */
    public static function dotsStr($str, $len, $dots = '...') {
        if( mb_strlen( $str, "UTF-8" ) <= $len ) {
            return $str;
        }
        else {
            return mb_substr( $str, 0, $len - 3, 'UTF-8' ) . $dots;
        }
    }

    /**
     * 替换html标签<
     * 
     * @param array $allowTags 允许的html标签，如 [img, embed]
     */
    public static function replaceHtmlTags($str, $allowTags = NULL) {
        if( empty( $allowTags ) ) {
            $str = strtr( $str, array (
                    "<" => "&lt;",
                    ">" => "&gt;" 
            ) );
        }
        else {
            // $exp = "/<(?!(img)|(strong)|(embed))/i";
            $expTags = array ();
            foreach ( $allowTags as $tag ) {
                array_push( $expTags, '(\/?' . $tag . ')' );
            }
            $exp = "/<(?!" . implode( "|", $expTags ) . ")/i";
            $str = preg_replace( $exp, "&lt;", $str );
        }
        return $str;
    }

    /**
     * 汉字转拼音<br>
     * 数据文件转自http://wordpress.org/plugins/pinyin-seo/
     * 
     * @param string $chinese 汉字
     * @param string $dash 拼音间隔，如'-'，默认空
     * @param bool $checkDYZ 是否检测多音字，默认true
     * @return string 拼音，如hanzi
     */
    public static function pinYin($chinese, $dash = '', $checkDYZ = true) {
        $chinese = trim( $chinese );
        $rtn = '';
        // 数据文件
        $pyDataFile = dirname( __FILE__ ) . '/../data/pinyin.dat';
        // 检查多音字
        if( $checkDYZ ) {
            // 多音字数据文件
            $dyzDataFile = dirname( __FILE__ ) . '/../data/duoyinzi.dat';
            if( is_file( $dyzDataFile ) ) {
                if( empty( self::$DYZ_DICT ) ) {
                    $fp = fopen( $dyzDataFile, 'r' );
                    while ( !feof( $fp ) ) {
                        $line = trim( fgets( $fp ) );
                        if( substr( $line, 0, 2 ) === '//' ) {
                            continue;
                        }
                        // 一个多音字必须放在2个汉字组成的词语中
                        self::$DYZ_DICT [mb_substr( $line, 0, 2, 'UTF-8' )] = mb_substr( $line, 2, NULL, 'UTF-8' );
                    }
                    fclose( $fp );
                }
                // 遍历多音字
                $newStr = '';
                $len = utf8len( $chinese );
                for($i = 0; $i < $len; $i++) {
                    $char = mb_substr( $chinese, $i, 1, 'UTF-8' );
                    // 判断是否属于东亚字符集
                    if( ord( $char ) > 128 ) {
                        $word = mb_substr( $chinese, $i, 2, 'UTF-8' );
                        // 如果该词语的多音字存在
                        if( isset( self::$DYZ_DICT [$word] ) ) {
                            $newStr .= str_replace( '-', $dash, self::$DYZ_DICT [$word] );
                            // 跳过词语
                            $i += 1;
                        }
                        else {
                            $newStr .= $char;
                        }
                    }
                    else {
                        $newStr .= $char;
                    }
                }
                $chinese = $newStr;
            }
            else {
                trigger_error( '多音字数据文件不存在' );
            }
        }
        if( is_file( $pyDataFile ) ) {
            if( empty( self::$PIN_YIN_DICT ) ) {
                $fp = fopen( $pyDataFile, 'r' );
                while ( !feof( $fp ) ) {
                    $line = trim( fgets( $fp ) );
                    if( substr( $line, 0, 2 ) === '//' ) {
                        continue;
                    }
                    // unicode中汉字为3个字节
                    self::$PIN_YIN_DICT [mb_substr( $line, 0, 1, 'UTF-8' )] = mb_substr( $line, 1, NULL, 'UTF-8' );
                }
                fclose( $fp );
            }
        }
        else {
            trigger_error( '拼音数据文件不存在' );
        }
        // 按字节遍历转换
        $len = utf8len( $chinese );
        $words = array ();
        // 临时英文词组
        $tmpEWord = '';
        for($i = 0; $i < $len; $i++) {
            $char = mb_substr( $chinese, $i, 1, 'UTF-8' );
            // 非汉字的只保留字母和数字
            if( preg_match( '/[a-z0-9]/i', $char ) ) {
                // 临时英文词组
                $tmpEWord .= $char;
            }
            else {
                if( $tmpEWord != '' ) {
                    array_push( $words, $tmpEWord );
                    $tmpEWord = '';
                }
                
                // 判断是否属于东亚字符集
                if( ord( $char ) > 128 ) {
                    if( $tmpEWord != '' ) {
                        array_push( $words, $tmpEWord );
                        $tmpEWord = '';
                    }
                    // 如果该汉字的拼音存在
                    if( isset( self::$PIN_YIN_DICT [$char] ) ) {
                        array_push( $words, self::$PIN_YIN_DICT [$char] );
                    }
                    else {
                        trigger_error( "字'{$char}'没有找到对应拼音", E_USER_WARNING );
                        // array_push($words, $char);
                    }
                }
                else {
                    // trigger_error("字符'{$char}'被剔除", E_USER_WARNING);
                }
            }
        }
        $rtn = implode( $dash, $words );
        return $rtn;
    }
}