<?php
/**
 *
 * 操作工具类
 * @author  wdy
 * @date    2016-01-11
 */

namespace App\Util;

class Tool
{
    /**
     * 写日志
     * @param $text
     * @param string $title
     * @param string $file
     * @param null $params+
     */
    public static function writeLog($text, $title = '', $file = '/logs/', $params = null)
    {
        $name = date('Ymd', time()) . ".txt";
        $file = public_path() . $file;

        if (!is_dir($file))        //路径若不存在则创建
            FileUtil::mk_dirs($file);

        $file .= $name;

        date_default_timezone_set('PRC');
        $handler = fopen($file, 'a');

        $content = "================" . $title . "=========" . date('Y-m-d H:i:s') . "==========\r\n";

        $content .= $text . "\r\n";

        if (null != $params)
            $content .= self::getArrayString($params) . "\r\n";

        fwrite($handler, $content);
        fclose($handler);
    }

    /**
     * 数组转为string
     * @param $params
     * @return string
     */
    public static function getArrayString($params)
    {
        $str = '';
        foreach ($params as $k => $v) {
            if (!empty($str))
                $str .= ",";
            $str .= "{$k}={$v}";
        }

        return urldecode($str);
    }

}