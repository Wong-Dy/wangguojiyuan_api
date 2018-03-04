<?php
/**
 * 模型
 * User: wdy
 * Date: 2018/01/10
 * Time: 22:49
 */

namespace App\Models;


class GameBbs extends Base
{
    protected $table = 'tab_game_bbs';

    public function scopeValid($query)
    {
        return $query->where('cl_Status', 1);
    }

    public function user()
    {
        return $this->hasOne('App\Models\User', 'user_id', 'cl_UserId');
    }

    public function comment()
    {
        return $this->hasMany('App\Models\GameBbsComment', 'cl_BbsId', 'cl_Id');
    }

    public function scopeOrderType($q, $orderType = 0)
    {
        if ($orderType == 1) //热度排序
            $q->orderby('cl_Comment', 'desc')->orderby('cl_Like', 'desc')->orderby('cl_CreateTime', 'desc');
        else if ($orderType == 2) //热门
            $q->orderby('cl_IsHot', 'desc')->orderby('cl_Comment', 'desc')->orderby('cl_Like', 'desc')->orderby('cl_CreateTime', 'desc');
        else
            $q->orderby('cl_CreateTime', 'desc');
    }

    public function getPhotoArr($isUrl = 1)
    {
        $list = explode(',', $this->cl_Photos);
        if ($isUrl) {
            $urlList = [];
            foreach ($list as $item) {
                $urlList [] = CUSTOM_API_HTTPS_HOST . $item;
            }
            return $urlList;
        }
        return $list;
    }

}