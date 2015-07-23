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
 * 日期辅助类
 *
 * @author Ather.Shu Nov 9, 2014 7:23:38 PM
 */
class DateUtil {
    // 间隔：日
    const INTERVAL_DAY = 1;
    // 间隔：周
    const INTERVAL_WEEK = 2;
    // 间隔：月
    const INTERVAL_MONTH = 3;
    // 间隔：季度
    const INTERVAL_QUARTER = 4;

    /**
     * 设置时区并打印当前时间字串
     *
     * @param string $format 参见php api date()
     * @param int $timestamp 参见php api date()，默认当前时刻time()
     * @param string $timezone 时区，默认是东八区，如Asia/Shangha
     * @return string
     */
    public static function localDate($format, $timestamp = NULL, $timezone = NULL) {
        $otz = date_default_timezone_get();
        if( $timezone == NULL ) {
            $timezone = "Asia/Shanghai";
        }
        date_default_timezone_set( $timezone );
        if( $timestamp == NULL ) {
            $timestamp = time();
        }
        // 输出时间字串
        $rtn = ($timestamp == NULL ? date( $format ) : date( $format, $timestamp ));
        date_default_timezone_set( $otz );
        return $rtn;
    }

    /**
     * 根据间隔类型以及与当前间隔长度计算起止时间
     *
     * @param int $intervalType 间隔类型，天，周，月
     * @param int $interval 0代表当前，负数代表过去，正数代表将来
     * @param string $dateFormat 时间格式
     * @return array ['range', 'start', 'end']
     */
    public static function calcRange($intervalType, $interval, $dateFormat = "Y-m-d") {
        if( $intervalType == self::INTERVAL_QUARTER ) {
            $interval *= 3;
        }
        $interval = ($interval >= 0) ? "+{$interval}" : "{$interval}";
        switch ($intervalType) {
            case self::INTERVAL_DAY :
                $startTime = strtotime( "{$interval} days 00:00:00" );
                $endTime = strtotime( "{$interval} days 23:59:59" );
                break;
            case self::INTERVAL_WEEK :
                $startTime = strtotime( "Monday this week 00:00:00", strtotime( "{$interval} weeks" ) );
                $endTime = strtotime( "Sunday this week 23:59:59", strtotime( "{$interval} weeks" ) );
                break;
            case self::INTERVAL_MONTH :
            case self::INTERVAL_QUARTER :
                $startTime = strtotime( "first day of {$interval} months 00:00:00" );
                $endTime = strtotime( "last day of {$interval} months 23:59:59" );
                break;
        }
        $endTime = min( $endTime, time() );
        $range = ($intervalType == self::INTERVAL_DAY) ? date( $dateFormat, $startTime ) : (date( $dateFormat, $startTime ) . ' ~ ' . date( 
                $dateFormat, $endTime ));
        return [ 
            'range' => $range,
            'start' => $startTime,
            'end' => $endTime 
        ];
    }

    /**
     * 智能格式化，返回如 刚刚 1分钟前 半小时前等
     *
     * @param int $timestamp
     * @param string $format
     * @return string
     */
    public static function intelligentFormat($timestamp, $format = "Y-m-d") {
        $now = time();
        $gap = $now - $timestamp;
        $minute = 60;
        $hour = 60 * $minute;
        $day = 24 * $hour;
        if( $gap > 0 && $gap < $hour ) {
            $multiper = floor( $gap / $minute );
            return $multiper == 0 ? '刚刚' : "{$multiper}分钟前";
        }
        else if( $gap > 0 && $gap < $day ) {
            $multiper = floor( $gap / $hour );
            return "{$multiper}小时前";
        }
        return date( $format, $timestamp );
    }
}