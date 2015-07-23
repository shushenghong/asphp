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
 * 数组辅助类
 *
 * @author Ather.Shu Nov 9, 2014 7:13:57 PM
 */
class ArrayUtil {

    /**
     * 判断PHP数组是否纯数字索引数组（列表/向量表）
     */
    public static function is_list($arr) {
        if( !is_array( $arr ) ) {
            return false;
        }
        else if( empty( $arr ) ) {
            return true;
        }
        else {
            $key_is_nums = array_map( 'is_numeric', array_keys( $arr ) );
            return array_reduce( $key_is_nums, create_function( '$x,$y', 'return $x&&$y;' ), true );
        }
    }
}