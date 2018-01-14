<?php
/**
 * 工具类
 * User: wdy
 * Date: 2017/11/14
 * Time: 10:58
 */

namespace App\Util;

class ArrayUtil
{
    /**
     * 用给定的值填充数组
     * @param $data
     * @param $count
     * @return array
     */
    public static function fill($data, $count, $start_index = 0)
    {
        return array_fill($start_index, $count, $data);
    }
}