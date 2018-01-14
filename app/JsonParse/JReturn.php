<?php
/**
 * 生成返回json类
 * @author  wdy
 * @date    2016-01-11
 */

namespace App\JsonParse;

class JReturn
{
    public static function success()
    {
        return json_encode(['errcode' => JErrorCode::SUCCESS, 'errmsg' => self::getErrorInfo(JErrorCode::SUCCESS)]);
    }

    public static function result($code, $param = '', $msg = '', $dataList = '')
    {
        if (empty($msg))
            $msg = self::getErrorInfo($code);
        if (!empty($dataList))
            $param['dataList'] = $dataList;
        return json_encode(['errcode' => $code, 'errmsg' => $msg, 'data' => $param]);
    }

    public static function error($code = 0, $msg = '')
    {
        if (empty($msg))
            $msg = self::getErrorInfo($code);

        return json_encode(['errcode' => $code, 'errmsg' => $msg]);
    }

    public static function getErrorInfo($code)
    {
        $msg = null != JErrorCode::ERROR_INFO[$code] ? JErrorCode::ERROR_INFO[$code] : '';
        return $msg;
    }
}