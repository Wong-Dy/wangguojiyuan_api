<?php
/**
 * 模型
 * User: wdy
 * Date: 2018/01/10
 * Time: 22:49
 */

namespace App\Models;


class GameBbsLike extends Base
{
    protected $table = 'tab_game_bbs_like';

    public function bbs()
    {
        return $this->hasOne('App\Models\GameBbs', 'cl_Id', 'cl_BbsId');
    }

}