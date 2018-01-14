<?php
/**
 * 工具类
 * User: wdy
 * Date: 2017/11/14
 * Time: 10:58
 */

namespace App\Util;

class ConfigUtil
{
    public static function getUserChannel($no)
    {
        $arr = configCustom('user_channel');
        if (isset($arr[$no]))
            return $arr[$no];
        return '';
    }

    public static function getIsBaoLongChannel($no)
    {
        if ('baolong001' != $no)
            return false;
        return true;
    }
}