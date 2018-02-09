<?php
/**
 * 联盟成员 模型
 * User: wdy
 * Date: 2018/01/10
 * Time: 22:49
 */

namespace App\Models;

class GameGroupMember extends Base
{
    protected $table = 'tab_game_group_member';

    public function group()
    {
        return $this->hasOne('App\Models\GameGroup', 'cl_Id', 'cl_GroupId');
    }

    public function user()
    {
        return $this->hasOne('App\Models\User', 'user_id', 'cl_UserId');
    }
}