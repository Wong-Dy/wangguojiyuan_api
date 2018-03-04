<?php
/**
 * 基础实体类
 * User: Administrator
 * Date: 2016/4/27
 * Time: 11:32
 */

namespace App\Models;

use Exception,Eloquent;

class Base extends Eloquent
{
    protected $connection = 'mysql';
    protected $table = 'demo';
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "cl_Id";
    protected $guarded = [];

//    public function scopeValid($query)
//    {
//        return $query->where('cl_Status', 1);
//    }

    public function scopePage($query,$index)
    {
        \Input::offsetSet('page',$index);
        return $query;
    }

    public function scopeMulti($query, $arr)
    {
        if (!is_array($arr)) {
            return $query;
        }

        foreach ($arr as $key => $value) {
            if ("" != trim($value))
                $query = $query->where($key, $value);
        }
        return $query;
    }

}