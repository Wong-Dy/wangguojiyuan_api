<?php
/**
 * 操作模型
 * User: wdy
 * Date: 2018/01/27
 * Time: 19:25
 */

namespace App\Models;

use App\Util\TimeUtil;

class ServiceLog extends Base
{
    protected $table = 'tab_service_log';

    public static function add($title, $content, $result = '', $userId = 0, $type = 1, $remark = '', $status = 1, $source = 'submail')
    {
        $model['cl_Title'] = $title;
        $model['cl_Type'] = $type;
        $model['cl_Content'] = $content;
        $model['cl_Remark'] = $remark;
        $model['cl_Result'] = is_string($result) ? $result : json_encode($result);
        $model['cl_UserId'] = $userId;
        $model['cl_Source'] = $source;
        $model['cl_Status'] = $status;
        $model['cl_CreateTime'] = TimeUtil::getChinaTime();
        return self::create($model);
    }
}