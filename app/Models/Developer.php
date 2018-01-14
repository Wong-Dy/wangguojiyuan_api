<?php
/**
 * 接口帐号管理表，操作模型
 * User: wdy
 * Date: 2016/2/2
 * Time: 9:53
 */

namespace App\Models;

class Developer extends Base
{
    protected $table = 'tab_developer';

    public function getTypeDesc()
    {
        return isset(getSelectList(SL_ENCRYMODEL)[$this->cl_Type]) ? getSelectList(SL_ENCRYMODEL)[$this->cl_Type] : "未知";
    }

    public function getStatusDesc()
    {
        return isset(getSelectList(SL_STATUS)[$this->cl_Status]) ? getSelectList(SL_STATUS)[$this->cl_Status] : "未知";
    }

    public static function selectStatusList()
    {
        return getSelectList(SL_STATUS);
    }

    public static function selectTypeList()
    {
        return getSelectList(SL_ENCRYMODEL);
    }
}