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

    public function comment()
    {
        return $this->hasMany('App\Models\GameBbsComment', 'cl_BbsId', 'cl_Id');
    }

}