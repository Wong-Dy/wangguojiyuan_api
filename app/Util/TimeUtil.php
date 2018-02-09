<?php
/**
 * 时间工具类
 * User: wdy
 * Date: 2016/1/11
 * Time: 17:34
 */

namespace App\Util;

use Carbon\Carbon;

class TimeUtil
{
    /**
     * 获取中国上海时区当前时间
     * @param string $format
     * @return bool|string
     */
    public static function getChinaTime($format = "Y-m-d H:i:s")
    {
        $timezone_out = date_default_timezone_get();
        date_default_timezone_set('Asia/Shanghai');
        $chinaTime = date($format);
        date_default_timezone_set($timezone_out);
        return $chinaTime;
    }

    /**
     * 获取最早默认时间
     * @return string
     */
    public static function getDefaultTime()
    {
        return '1970-1-1';
    }

    /**
     * 获取明天的日期
     * @return bool|string
     */
    public static function getTomorrowTime()
    {
        return self::increaseTime(self::getChinaTime('Y-m-d'));
    }

    /**
     * 时间增加
     * @param $time
     * @param int $num
     * @param string $type 时间类型 $type ：day months year
     * @return bool|string
     */
    public static function increaseTime($time, $num = 1, $type = "day", $format = "Y-m-d H:i")
    {
        $time .= " +$num $type";
        return self::parseTime($time, $format);
    }

    /**
     * 时间减少
     * @param $time
     * @param int $num
     * @param string $type 时间类型 $type ：day months year
     * @return bool|string
     */
    public static function decreaseTime($time, $num = 1, $type = "day", $format = "Y-m-d H:i")
    {
        $time .= " -$num $type";
        return self::parseTime($time, $format);
    }

    /**
     * 根据时间格式转换时间
     * @param $time
     * @param string $format 时间格式 $format
     * @return bool|string
     */
    public static function parseTime($time, $format = "Y-m-d H:i")
    {
        return date($format, strtotime($time));
    }

    /**
     * 转换日期范围
     * @param $strtime 为空返回默认范围
     * @param string $format 范围分隔符
     * @return array (格式:2000/1/1 - 2001-1-1)
     */
    public static function parseDateRange($strtime, $lastTime = null, $format = " - ")
    {
        if (empty($lastTime))
            return explode($format, !empty($strtime) ? $strtime : self::getDefaultTime() . $format . self::getTomorrowTime());
        else
            return explode($format, !empty($strtime) ? $strtime : self::getDefaultTime() . $format . $lastTime);
    }

    /**
     * 转换日期条件
     * @param $strtime
     * @param $lastTime
     * @param int $type (1日期类型Y-m-d,0时间戳)
     * @param string $dz 时区 默认中国
     * @return array
     */
    public static function parseMuiDateRange($strtime, $lastTime, $type = 0, $dz = 'PRC')
    {
        if ($type == 1)
            return [(empty($strtime) ? self::getDefaultTime() : $strtime), (empty($lastTime) ? self::getTomorrowTime() : $lastTime)];
        else
            return [(empty($strtime) ? Carbon::parse(self::getDefaultTime(), $dz)->getTimestamp() :
                Carbon::parse($strtime, $dz)->getTimestamp()),
                (empty($lastTime) ? Carbon::parse(self::getTomorrowTime(), $dz)->getTimestamp() :
                    Carbon::parse($lastTime . ((strstr($lastTime, ':') == false) ? ' 23:59:59' : ''), $dz)->getTimestamp())];

    }

    public static function parseTimestamp($datetime, $dz = 'PRC')
    {
        return Carbon::parse($datetime, $dz)->getTimestamp();
    }

    public static function parseTimestampToDateTime($timestamp, $format = 'Y-m-d H:i:s', $dz = 'PRC')
    {
        if (empty($timestamp))
            return '';
        date_default_timezone_set($dz);
        return date($format, $timestamp);
    }

    public static function  parseDetailTime($time)
    {
        $time = self::parseTime($time, 'Y-m-d') . ' 23:59:59';
        return $time;
    }

    /**
     * 计算两个日期之差
     * @param $time1
     * @param $time2
     * @return float
     */
    public static function dateSubtract($time1, $time2)
    {
        $Date_1 = self::ParseTime($time1, 'Y-m-d');
        $Date_2 = self::ParseTime($time2, 'Y-m-d');
        $Date_List_a1 = explode("-", $Date_1);
        $Date_List_a2 = explode("-", $Date_2);
        $d1 = mktime(0, 0, 0, $Date_List_a1[1], $Date_List_a1[2], $Date_List_a1[0]);
        $d2 = mktime(0, 0, 0, $Date_List_a2[1], $Date_List_a2[2], $Date_List_a2[0]);
        $Days = round(($d1 - $d2) / 3600 / 24);
        return $Days;
    }

}