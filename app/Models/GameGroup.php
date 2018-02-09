<?php
/**
 * 联盟 模型
 * User: wdy
 * Date: 2018/01/10
 * Time: 22:49
 */

namespace App\Models;

class GameGroup extends Base
{
    protected $table = 'tab_game_group';

    public function members()
    {
        return $this->hasMany('App\Models\GameGroupMember', 'cl_GroupId', 'cl_Id');
    }
}