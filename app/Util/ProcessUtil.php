<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/12/27
 * Time: 10:10
 */

namespace App\Util;

use App\Reference\SupplierReference;

class ProcessUtil
{
    public static $skipNum = 20;

    public static function setProcess($key, $value)
    {
        SupplierReference::setProcess($key, $value);
    }

    public static function  getProcess($key)
    {
        return SupplierReference::getProcess($key);
    }

    public static function setCalculateProcess($key, $total, $num)
    {
        $value = 0;
        if ($num == $total) {
            $value = 100;
        } elseif ($num % self::$skipNum == 0)
            $value = $num / $total * 100;
        if (!empty($value))
            self::setProcess($key, $value);
    }
}