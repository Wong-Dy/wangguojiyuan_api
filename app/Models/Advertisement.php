<?php
/**
 * 广告表，操作模型
 * User: wdy
 * Date: 2016/03/24
 * Time: 11:25
 */

namespace App\Models;

class Advertisement extends Base
{
    protected $table = 'tab_advertisement';

    public function scopeValid($query)
    {
        return $query->where('cl_Status', 1);
    }

    public function adposition()
    {
        return $this->hasOne('App\Models\AdPosition', 'cl_Id', 'cl_PId');
    }

    public function getStatusDesc()
    {
        return isset(getSelectList(SL_STATUS)[$this->cl_Status]) ? getSelectList(SL_STATUS)[$this->cl_Status] : "未知";
    }

    public function getTypeDesc()
    {
        return isset(getSelectList(SL_ADVERTISEMENT_TYPE)[$this->cl_Type]) ? getSelectList(SL_ADVERTISEMENT_TYPE)[$this->cl_Type] : "未知";
    }
}