<?php
/**
 * API错误日志操作模型
 * User: wdy
 * Date: 2017/09/25
 * Time: 12:41
 */

namespace App\Models;

use App\Util\TimeUtil;

class ApiErrorLog extends Base
{
    protected $table = 'tab_api_errorlog';

    /**
     * @param $log
     * @param string $param
     * @param int $type 0普通,1查询,2添加,3修改,4删除,99异常
     * @param string $class
     * @param string $function
     */
    public static function log($log, $param = '', $type = 0, $class = '', $function = '')
    {
        //type 0普通,1查询,2添加,3修改,4删除,99异常
        $data['cl_Type'] = $type;
        $data['cl_Log'] = $log;
        $data['cl_Param'] = json_encode($param);
        $data['cl_Class'] = $class;
        $data['cl_Function'] = $function;
        $data['cl_CreateTime'] = TimeUtil::getChinaTime();
        $ret = self::create($data);
        return $ret;
    }

    public static function q_normal($log, $param = '', $class = '', $function = '')
    {
        return self::log($log, $param, 0, $class, $function);
    }

    public static function q_search($log, $param = '', $class = '', $function = '')
    {
        return self::log($log, $param, 1, $class, $function);
    }

    public static function q_insert($log, $param = '', $class = '', $function = '')
    {
        return self::log($log, $param, 2, $class, $function);
    }

    public static function q_update($log, $param = '', $class = '', $function = '')
    {
        return self::log($log, $param, 3, $class, $function);
    }

    public static function q_delete($log, $param = '', $class = '', $function = '')
    {
        return self::log($log, $param, 4, $class, $function);
    }

    public static function q_exception($log, $param = '', $class = '', $function = '')
    {
        return self::log($log, $param, 99, $class, $function);
    }
}