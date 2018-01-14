<?php
/**
 * 基本实体类 （示例）
 * User: wdy
 * Date: 2016/4/27
 * Time: 11:01
 */

namespace App\Models;


class DemoModel extends Base
{
    protected $table = 'demo';

    public function getTypeDesc()
    {
        return isset(getSelectList("define")[$this->cl_Type]) ? getSelectList("define")[$this->cl_Type] : "未知";
    }

    public function getStatusDesc()
    {
        return isset(getSelectList(SL_STATUS)[$this->cl_Status]) ? getSelectList(SL_STATUS)[$this->cl_Status] : "未知";
    }

    public function scopeValid($query)
    {
        return $query->where('cl_Status', 1);
    }

    public function group()
    {
        return $this->hasOne('App\Models\Group', 'foreign_key', 'local_key');
    }

    public static function selectList(){
        return self::valid()->lists('cl_Name','cl_Id');
    }

    public static function selectStatusList()
    {
        return getSelectList(SL_STATUS);
    }

    public static function selectTypeList()
    {
        return getSelectList(SL_STATUS);
    }
}