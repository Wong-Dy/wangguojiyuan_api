<?php
/**
 * 共用方法类
 * @author  wdy
 * @date    2016-01-11
 */

namespace App\Util;

use Request as Input;
use Exception;
use Session;

class Comm
{
    // 日志路径
    const LOG_PATH = DEFAULT_LOG_PATH . "/Comm/";

    //==========================================
    // 设置Session值
    //==========================================
    public static function setSession($key, $value)
    {
        Session::put($key, $value);
    }

    //==========================================
    // 获取Session值
    //==========================================
    public static function getSession($key)
    {
        return Session::get($key);
    }

    //==========================================
    // steClass转成array
    //==========================================
    public static function object_array($array)
    {
        if (is_object($array))
            $array = (array)$array;

        if (is_array($array)) {
            foreach ($array as $key => $value) {
                $array[$key] = self::object_array($value);
            }
        }
        return $array;
    }

    //==========================================
    // 获取指定array中key数据，结果转换一维数组，只保留值
    // 原数据      $array
    // 名称   $name
    //==========================================
    public static function object_ArrayValue($array, $name)
    {
        $arr = self::object_array($array);
        $values = array();
        foreach ($arr as $key => $value) {
            $values[] = $value[$name];
        }
        return $values;
    }

    //==========================================
    // 获取data集合里name属性值(data["name"])
    // 数据 $data
    // 名称  $name
    //==========================================
    public static function getArrayKey($data, $name, $default = "")
    {
        try {
            if (isset($data[$name]))
                return $data[$name];

        } catch (Exception $ex) {
            return $default;
        }
        return $default;
    }

    /**
     * 获取obj class 的值 根据 name 变量名
     * @param $obj
     * @param $name
     * @param string $default
     * @return string
     */
    public static function getObjParam($obj, $name, $default = '')
    {
        if (!isset($obj) || empty($obj) || !isset($obj->$name) || empty($obj->$name))
            return $default;

        return $obj->$name;

    }

    public static function arrayAdd($arr, $item, $location = 'start')
    {
        if ('start' == $location) {
            $newArr[key($item)] = $item[key($item)];
            foreach ($arr as $key => $value) {
                $newArr[$key] = $value;
            }
            return $newArr;
        } else if ('end' == $location) {
            $arr[] = $item;
        }
        return $arr;
    }

    //==========================================
    // 金额分转成十进制
    // 金额  $fee
    //==========================================
    public static function getDisplayFee($fee)
    {
        $retFee = $fee / 100;
        return $retFee;
    }

    //==========================================
    // 金额十进制转成分
    // 金额 $fee
    //==========================================
    public static function getWriteFee($fee)
    {
        $retFee = $fee * 100;
        return $retFee;
    }

    //==========================================
    // 创建GUID
    //==========================================
    public static function getGuid()
    {
        $charid = strtoupper(md5(uniqid(mt_rand(), true)));
        $hyphen = chr(45);// "-"
        $uuid = chr(123)// "{"
            . substr($charid, 0, 8) . $hyphen
            . substr($charid, 8, 4) . $hyphen
            . substr($charid, 12, 4) . $hyphen
            . substr($charid, 16, 4) . $hyphen
            . substr($charid, 20, 12)
            . chr(125);// "}"
        $uuid = str_replace("}", "", str_replace("{", "", $uuid));
        $uuid = str_replace("-", "", $uuid);
        return $uuid;
    }

    //==========================================
    // 生成随机数
    //==========================================
    public static function make_rand($length = 32, $str = "0123456789")
    {
        //$str="0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $result = "";
        for ($i = 0; $i < $length; $i++) {
            $num[$i] = rand(0, strlen($str) - 1);
            $result .= $str[$num[$i]];
        }
        return $result;
    }

    /**
     * 设置json返回错误格式
     * @param string $msg 错误信息
     * @param bool $bool 成功失败
     * @param null $data 参数
     * @return string
     */
    public static function getJsonError($msg = "", $bool = true, $data = null)
    {
        return json_encode(["error" => $msg, "ret" => $bool, "data" => $data], JSON_UNESCAPED_UNICODE);
    }


    /**
     * 三十六进制数转换成十机制数
     * @param $char (string)$char 三十六进制数
     * @return mixed    返回：十进制数
     */
    public static function get_char36to10num($char)
    {
        $sum = 0;
        $array = array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z");
        $len = strlen($char);
        for ($i = 0; $i < $len; $i++) {
            $index = array_search($char[$i], $array);
            $sum += ($index + 1) * pow(36, $len - $i - 1);
        }
        return $sum - 1;
    }

    public static function  jsonEncodeUnicode($data)
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE);//\uXXXX 转中文
    }
}

