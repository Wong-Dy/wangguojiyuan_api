<?php
/**
 * 模型
 * User: wdy
 * Date: 2018/01/10
 * Time: 22:49
 */

namespace App\Models;


class GameBbsComment extends Base
{
    protected $table = 'tab_game_bbs_comment';

    public function scopeValid($query)
    {
        return $query->where('cl_Status', 1);
    }

    public function bbs()
    {
        return $this->hasOne('App\Models\GameBbs', 'cl_Id', 'cl_BbsId');
    }

    public function user()
    {
        return $this->hasOne('App\Models\User', 'user_id', 'cl_UserId');
    }

    public function touser()
    {
        return $this->hasOne('App\Models\User', 'user_id', 'cl_ToUserId');
    }

}