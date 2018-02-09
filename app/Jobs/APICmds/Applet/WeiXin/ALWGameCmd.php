<?php
/**
 * User: Administrator
 * Date: 2016/12/15
 * Time: 15:34
 */

namespace App\Jobs\APICmds\Applet\WeiXin;


use App\JsonParse\JErrorCode;
use App\Models\GameGroup;
use App\Models\GameGroupMember;
use App\Models\WXUser;
use App\Util\Comm;
use App\Util\TimeUtil;
use Cache;

class ALWGameCmd extends BaseCmd
{
    public function __construct($jsonData)
    {
        parent::__construct($jsonData);
        $this->logPath .= 'user/';
    }

    public function createGroup()
    {
        $data = $this->jsonData;
        try {
            if (!isset($data->name))
                return $this->error(JErrorCode::LACK_PARAM_ERROR);

            $wxUser = WXUser::where('cl_OpenId', $data->m_openId)->first();
            if (empty($wxUser))
                return $this->error(JErrorCode::WX_USER_INFO_NOT_FOUND_ERROR);

            $user = $wxUser->user;
            $modelData['cl_CreateTime'] = TimeUtil::getChinaTime();
            $modelData['cl_Creator'] = $user->user_id;
            $modelData['cl_Name'] = $data->name;
            $modelData['cl_District'] = isset($data->district) ? $data->district : 0;
            $modelData['cl_LocationX'] = isset($data->locationX) ? $data->locationX : 0;
            $modelData['cl_LocationY'] = isset($data->locationY) ? $data->locationY : 0;
            $ret = GameGroup::create($modelData);
            if (!$ret)
                return $this->error(JErrorCode::CUSTOM_ADD_ERROR);

            return $this->success();
        } catch (\Exception $e) {
            return $this->exception($e);
        }
    }

    public function updateGroup()
    {
        $data = $this->jsonData;
        try {
            if (!isset($data->groupId))
                return $this->error(JErrorCode::LACK_PARAM_ERROR);

            $wxUser = WXUser::where('cl_OpenId', $data->m_openId)->first();
            if (empty($wxUser))
                return $this->error(JErrorCode::WX_USER_INFO_NOT_FOUND_ERROR);

            $user = $wxUser->user;

            $model = GameGroup::find($data->groupId);
            if (empty($model))
                return $this->error(JErrorCode::CUSTOM_SELECT_NOT_FOUND);

            if ($user->user_id != $model->cl_Master)
                return $this->error(JErrorCode::INVALID_AUTH_ERROR);

            if (isset($data->name) && !empty($data->name))
                $modelData['cl_Name'] = $data->name;
            if (isset($data->district) && !empty($data->district))
                $modelData['cl_District'] = $data->district;
            if (isset($data->locationX) && !empty($data->locationX))
                $modelData['cl_LocationX'] = $data->locationX;
            if (isset($data->locationY) && !empty($data->locationY))
                $modelData['cl_LocationY'] = $data->locationY;
            if (isset($data->notice) && !empty($data->notice))
                $modelData['cl_Notice'] = $data->notice;

            $ret = GameGroup::update($modelData);
            if (!$ret)
                return $this->error(JErrorCode::CUSTOM_UPDATE_ERROR);

            return $this->success();
        } catch (\Exception $e) {
            return $this->exception($e);
        }
    }

    public function getUserGroup()
    {
        $data = $this->jsonData;
        try {
            $wxUser = WXUser::where('cl_OpenId', $data->m_openId)->first();
            if (empty($wxUser))
                return $this->error(JErrorCode::WX_USER_INFO_NOT_FOUND_ERROR);

            $user = $wxUser->user;

            $groupMember = GameGroupMember::where('cl_UserId', $user->user_id)->first();
            if (empty($groupMember))
                return $this->error(JErrorCode::CUSTOM_SELECT_NOT_FOUND);

            $group = $groupMember->group;

            $this->result_param['groupName'] = $group->cl_Name;
            $this->result_param['groupNotice'] = $group->cl_Notice;
            $this->result_param['district'] = $group->cl_District;
            $this->result_param['locationX'] = $group->cl_LocationX;
            $this->result_param['locationY'] = $group->cl_LocationY;
            $this->result_param['master'] = $group->cl_Master;

            $this->result_param['level'] = $groupMember->cl_Level;

            foreach ($group->members()->orderby('cl_Level', 'desc')->get() as $item) {
                $result_item = $this->std();

                $result_item->userId = $item->cl_UserId;
                $result_item->alias = $item->user->alias;
                $result_item->gameNickName = $item->user->gameinfo->cl_NickName;
                $result_item->level = $item->cl_Level;

                $this->result_list[] = $result_item;
            }

            return $this->result();
        } catch (\Exception $e) {
            return $this->exception($e);
        }
    }

    public function getGroupInviteCode()
    {
        $data = $this->jsonData;
        try {
            $wxUser = WXUser::where('cl_OpenId', $data->m_openId)->first();
            if (empty($wxUser))
                return $this->error(JErrorCode::WX_USER_INFO_NOT_FOUND_ERROR);

            $user = $wxUser->user;

            $groupMember = GameGroupMember::where('cl_UserId', $user->user_id)->first();
            if (empty($groupMember))
                return $this->error(JErrorCode::CUSTOM_SELECT_NOT_FOUND);

            $group = $groupMember->group;


            if (empty($group->cl_InviteTime) || (time() - TimeUtil::parseTimestamp($group->cl_InviteTime)) > 60 * 60) {
                $group->cl_InviteTime = TimeUtil::getChinaTime();
                $group->cl_InviteCode = $group->cl_Id . Comm::make_rand(3);
            }

            $this->result_param['code'] = $group->cl_InviteCode;

            return $this->result();
        } catch (\Exception $e) {
            return $this->exception($e);
        }
    }

    public function joinGroup()
    {
        $data = $this->jsonData;
        try {
            if (!isset($data->inviteCode))
                return $this->error(JErrorCode::LACK_PARAM_ERROR);

            $wxUser = WXUser::where('cl_OpenId', $data->m_openId)->first();
            if (empty($wxUser))
                return $this->error(JErrorCode::WX_USER_INFO_NOT_FOUND_ERROR);

            $user = $wxUser->user;

            $group = GameGroup::where('cl_InviteCode', $data->inviteCode)->first();
            if (empty($group))
                return $this->error(JErrorCode::CUSTOM_SELECT_NOT_FOUND);

            if ((time() - TimeUtil::parseTimestamp($group->cl_InviteTime)) > 60 * 60) {
                return $this->error(JErrorCode::ERROR, '邀请码失效');
            }

            $memberData['cl_GroupId'] = $group->cl_Id;
            $memberData['cl_UserId'] = $user->user_id;
            $memberData['cl_Level'] = 1;
            $memberData['cl_CreateTime'] = TimeUtil::getChinaTime();

            $ret = GameGroupMember::create($memberData);
            if (!$ret)
                return $this->error(JErrorCode::CUSTOM_ADD_ERROR);

            return $this->success();
        } catch (\Exception $e) {
            return $this->exception($e);
        }
    }

}