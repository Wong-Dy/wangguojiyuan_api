<?php
/**
 * 广告位表，操作模型
 * User: jiangchuanyi
 * Date: 2016/03/24
 * Time: 11:25
 */

namespace App\Models;

class AdPosition extends Base
{
    protected $table = 'tab_adposition';

    public function scopeValid($query)
    {
        return $query->where('cl_Status', 1);
    }

    public function getStatusDesc()
    {
        return isset(getSelectList(SL_STATUS)[$this->cl_Status]) ? getSelectList(SL_STATUS)[$this->cl_Status] : "未知";
    }

    public function getSystemDesc()
    {
        return isset(getSelectList(SL_SYSTEM)[$this->cl_System]) ? getSelectList(SL_SYSTEM)[$this->cl_System] : "未知";
    }

    public static function selectList()
    {
        $modelArr = self::select('cl_Id', 'cl_Name', 'cl_System')->get();
        $list[0] = '所有广告位';
        foreach ($modelArr as $item) {
            $list[$item->cl_Id] = $item->cl_Name . "【{$item->getSystemDesc()}】";
        }
        return $list;
    }

}