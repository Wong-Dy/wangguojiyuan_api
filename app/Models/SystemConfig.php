<?php
/**
 * 系统配置操作模型
 * User: wdy
 * Date: 2017/12/04
 * Time: 19:25
 */

namespace App\Models;

class SystemConfig extends Base
{
    protected $table = 'tab_system_config';

    public function parent()
    {
        return $this->hasOne('App\Models\SystemConfig', 'cl_Id', 'cl_ParentId');
    }
}