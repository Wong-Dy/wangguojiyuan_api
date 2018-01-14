<?php
/**
 * 生成序列号
 * User: wdy
 * Date: 2016/1/11
 * Time: 17:38
 */

namespace App\Util;

use Cache;

class SeqNo
{

    /**
     * （1位 平台或设备标识） + yyMMdd（6位） + 时分秒换算为秒（5位） +  4位自增编号（4位）
     *
     * @param string $name
     * @return string
     */
    public static function getOrderCode($name = CGlobal::SEQNO_DEFAULT, $prefix = 1)
    {
        if (!Cache::has($name)) {
            Cache::forever($name, 1);
        } else {
            if (Cache::get($name) > 9999)
                Cache::forever($name, 1);
            else
                Cache::forever($name, Cache::get($name) + 1);
        }

        $date = TimeUtil::getChinaTime("Y-m-d");
        $time = TimeUtil::getChinaTime("H:i:s");
        $his = explode(':', $time);

        return config('seq_no.' . $name, $prefix) . str_replace("-", "", $date) . ($his[0] * 360) . ($his[1] * 60) . $his[2] . sprintf("%04d", Cache::get($name));
    }


}