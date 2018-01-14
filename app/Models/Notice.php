<?php
/**
 * 系统公告通知表，操作模型
 * User: wdy
 * Date: 2016/2/2
 * Time: 9:54
 */

namespace App\Models;

class Notice extends Base
{
    protected $table = 'tab_notice';

    public function scopeValid($query)
    {
        return $query->whereRaw('cl_Status=1 and cl_FailureTime>curdate() ');
    }

    public function scopeType($query, $type)
    {
        return $query->where('cl_Type', $type);
    }

    public function scopeRecipient($query, $account)
    {
        if (empty($account))
            return $query;
        return $query->whereRaw('find_in_set(?, cl_Recipient)', [$account]);
    }

    public function getTypeDesc()
    {
        return getSelectList(SL_NOTICE_TYPE)[$this->cl_Type];
    }

    public function getStatusDesc()
    {
        return getSelectList(SL_STATUS)[$this->cl_Status];
    }

    public static function selectTypeList()
    {
        return getSelectList(SL_NOTICE_TYPE);
    }

    public static function selectStatusList()
    {
        return getSelectList(SL_STATUS);
    }
}