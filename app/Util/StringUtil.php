<?php
/**
 * 字符串工具类
 * User: wdy
 * Date: 2016/4/12
 * Time: 13:58
 */

namespace App\Util;

class StringUtil
{
    /**
     * 支持中文字符串截取
     */
    public static function msubstr($str, $start = 0, $length, $charset = "utf-8", $suffix = true)
    {
        switch ($charset) {
            case 'utf-8':
                $char_len = 3;
                break;
            case 'UTF8':
                $char_len = 3;
                break;
            default:
                $char_len = 2;
        }
        //小于指定长度，直接返回
        if (strlen($str) <= ($length * $char_len)) {
            return $str;
        }
        if (function_exists("mb_substr")) {
            $slice = mb_substr($str, $start, $length, $charset);
        } else if (function_exists('iconv_substr')) {
            $slice = iconv_substr($str, $start, $length, $charset);
        } else {
            $re['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
            $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
            $re['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
            $re['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
            preg_match_all($re[$charset], $str, $match);
            $slice = join("", array_slice($match[0], $start, $length));
        }
        if ($suffix)
            return $slice;
        return $slice;
    }

    /**
     * 计算中文字符串长度
     * @param null $string
     * @return int
     */
    public static function utf8_strLen($string = null)
    {
        // 将字符串分解为单元
        preg_match_all("/./us", $string, $match);
        // 返回单元个数
        return count($match[0]);
    }

    /**
     * 去掉所有空格,换行等
     * @param $str
     * @return mixed
     */
    public static function trimAll($str)
    {
        $qian = array(" ", "　", "\t", "\n", "\r");
        $hou = array("", "", "", "", "");
        return str_replace($qian, $hou, $str);
    }

    /**
     * 获取字符分隔数组，去重复
     *
     * @param $txt  根据符号组合的字符串("1,2,3")
     * @param string $char
     * @return string
     */
    public static function getSeparateUnique($txt, $char = ',')
    {
        return implode($char, array_unique(explode($char, trim($txt))));
    }

    /**
     * 替换隐藏字符 188****8888
     * @param $Content
     * @return string
     */
    public static function replaceHideStr($Content)
    {
        //截取中文必须是3的倍数
        return self::msubstr($Content, 0, strlen($Content) / 2) . str_repeat('*', 5);
    }

}