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
 * number辅助类
 * 
 * @author Ather.Shu Nov 9, 2014 7:56:40 PM
 */
class NumberUtil {

    /**
     * 判断数字是否在某区间
     * 
     * @param number $num 数字
     * @param number $min 最小值
     * @param number $max 最大值
     * @param boolean $allowMin 是否允许等于最小值
     * @param boolean $allowMax 是否允许等于最大值
     * @return boolean
     */
    public static function between($num, $min, $max, $allowMin = TRUE, $allowMax = TRUE) {
        return ($allowMin ? $num >= $min : $num > $min) && ($allowMax ? $num <= $max : $num < $max);
    }
}