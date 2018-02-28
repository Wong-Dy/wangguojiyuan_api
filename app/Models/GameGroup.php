<?php
/**
 * 联盟 模型
 * User: wdy
 * Date: 2018/01/10
 * Time: 22:49
 */

namespace App\Models;

use App\Util\Comm;
use App\Util\TimeUtil;

class GameGroup extends Base
{
    protected $table = 'tab_game_group';

    public function master()
    {
        return $this->hasOne('App\Models\User', 'user_id', 'cl_Master');
    }

    public function members()
    {
        return $this->hasMany('App\Models\GameGroupMember', 'cl_GroupId', 'cl_Id');
    }

    public function isMemberLimit(&$retMsg)
    {
        if ($this->members()->valid()->count() >= configCustom('group_member_limit')) {
            $retMsg = '联盟人数已满！';
            return true;
        }
        return false;
    }

    public function validInviteTime(&$retMsg = '')
    {
        if ((time() - TimeUtil::parseTimestamp($this->cl_InviteTime)) > configCustom('group_invite_time_limit') * 60) { // 限制时间期限
            $retMsg = '邀请码失效！';
            return false;
        }

        return true;
    }

    public function createInviteCode()
    {
        for ($i = 0; $i < 15; $i++) {
            $code = str_shuffle(Comm::make_rand(6, "0123456789abcdefghijklmnopqrstuvwxyz"));

            if (self::where('cl_InviteCode', $code)->where('cl_Id', '<>', $this->cl_Id)->count() < 1)
                continue;
            else
                $code = '';
        }
        if (empty($code))
            $code = $this->cl_Id . $this->cl_Master;

        $this->cl_InviteCode = $code;
        $this->cl_InviteTime = TimeUtil::getChinaTime();
        $this->save();

        return $code;
    }

}