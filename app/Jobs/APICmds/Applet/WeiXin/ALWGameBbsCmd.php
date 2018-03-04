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
use App\Models\ScanCode;
use App\Models\User;
use App\Models\UserNoticeRecord;
use App\Models\UserSystem;
use App\Models\WXUser;
use App\Service\RunService;
use App\Util\Comm;
use App\Util\TimeUtil;
use App\Util\Tool;
use Cache;

class ALWGameBbsCmd extends BaseCmd
{
    public function __construct($jsonData)
    {
        parent::__construct($jsonData);
        $this->logPath .= 'user/';
    }

    public function addGameBbs()
    {
        $data = $this->jsonData;
        try {
            if (!isset($data->content) || !isset($data->photoList))
                return $this->error(JErrorCode::LACK_PARAM_ERROR);

            $wxUser = WXUser::where('cl_OpenId', $data->m_openId)->first();
            if (empty($wxUser))
                return $this->error(JErrorCode::WX_USER_INFO_NOT_FOUND_ERROR);

            $user = $wxUser->user;

            $groupMember = GameGroupMember::valid()->where('cl_UserId', $user->user_id)->first();
            if ($groupMember) {
                return $this->errori('你已经有联盟了');
//                $group = $groupMember->group;
            }

            $modelData['cl_CreateTime'] = TimeUtil::getChinaTime();
            $modelData['cl_UpdateTime'] = TimeUtil::getChinaTime();
            $modelData['cl_Creator'] = $user->user_id;
            $modelData['cl_Master'] = $user->user_id;
            $modelData['cl_Name'] = $data->name;
            $modelData['cl_District'] = isset($data->district) ? $data->district : 0;
            $modelData['cl_LocationX'] = isset($data->locationX) ? $data->locationX : 0;
            $modelData['cl_LocationY'] = isset($data->locationY) ? $data->locationY : 0;
            $retId = GameGroup::insertGetId($modelData);
            if (!$retId)
                return $this->error(JErrorCode::CUSTOM_ADD_ERROR);

            $groupMemberData['cl_UserId'] = $user->user_id;
            $groupMemberData['cl_GroupId'] = $retId;
            $groupMemberData['cl_Level'] = 5;
            $groupMemberData['cl_CreateTime'] = TimeUtil::getChinaTime();
            GameGroupMember::create($groupMemberData);

            return $this->success();
        } catch (\Exception $e) {
            return $this->exception($e);
        }
    }

    public function updateGroup()
    {
        $data = $this->jsonData;
        try {
            $wxUser = WXUser::where('cl_OpenId', $data->m_openId)->first();
            if (empty($wxUser))
                return $this->error(JErrorCode::WX_USER_INFO_NOT_FOUND_ERROR);

            $user = $wxUser->user;

            $groupMember = GameGroupMember::valid()->where('cl_UserId', $user->user_id)->first();
            if (empty($groupMember))
                return $this->error(JErrorCode::CUSTOM_SELECT_NOT_FOUND);

            if ($groupMember->cl_Level < 5) {
                return $this->errori('无权限！');
            }

            $model = $groupMember->group;
            if (empty($model))
                return $this->error(JErrorCode::CUSTOM_SELECT_NOT_FOUND);

            if ($user->user_id != $model->cl_Master)
                return $this->error(JErrorCode::INVALID_AUTH_ERROR);

            $modelData = [];
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

            $modelData['cl_UpdateTime'] = TimeUtil::getChinaTime();

            $ret = $model->update($modelData);
            if (!$ret)
                return $this->error(JErrorCode::CUSTOM_UPDATE_ERROR);

            return $this->success();
        } catch (\Exception $e) {
            return $this->exception($e);
        }
    }

    public function updateGroupSetting()
    {
        $data = $this->jsonData;
        try {

            $wxUser = WXUser::where('cl_OpenId', $data->m_openId)->first();
            if (empty($wxUser))
                return $this->error(JErrorCode::WX_USER_INFO_NOT_FOUND_ERROR);

            $user = $wxUser->user;

            $groupMember = GameGroupMember::valid()->where('cl_UserId', $user->user_id)->first();
            if (empty($groupMember))
                return $this->error(JErrorCode::CUSTOM_SELECT_NOT_FOUND);

            if ($groupMember->cl_Level < 5) {
                return $this->errori('无权限！');
            }

            $group = $groupMember->group;

            if (isset($data->isShareJoin))
                $group->cl_IsShareJoin = $data->isShareJoin;
            if (isset($data->isInviteCodeJoin))
                $group->cl_IsInviteCodeJoin = $data->isInviteCodeJoin;

            $ret = $group->save();

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

            if (isset($data->groupId) && !empty($data->groupId))
                $group = GameGroup::find($data->groupId);
            else {
                $groupMember = GameGroupMember::valid()->where('cl_UserId', $user->user_id)->first();
                if (empty($groupMember))
                    return $this->error(JErrorCode::CUSTOM_SELECT_NOT_FOUND);

                $group = $groupMember->group;
            }

            if (empty($group))
                return $this->error(JErrorCode::CUSTOM_SELECT_NOT_FOUND);

            $this->result_param['groupName'] = $group->cl_Name;
            $this->result_param['groupNotice'] = $group->cl_Notice;
            $this->result_param['district'] = $group->cl_District;
            $this->result_param['locationX'] = $group->cl_LocationX;
            $this->result_param['locationY'] = $group->cl_LocationY;
            $this->result_param['master'] = $group->cl_Master;
            $this->result_param['masterName'] = $group->master->getGameName();
            $this->result_param['masterWxHeadUrl'] = $group->master->wxuser->headimgurl;
            $this->result_param['isShareJoin'] = $group->cl_IsShareJoin;
            $this->result_param['isInviteCodeJoin'] = $group->cl_IsInviteCodeJoin;
            $this->result_param['updateTime'] = TimeUtil::parseTime($group->cl_UpdateTime);

            if (isset($groupMember) && !empty($groupMember)) {
                $this->result_param['level'] = $groupMember->cl_Level;
            }
            $this->result_param['memberSum'] = $group->memberSum();


            return $this->result();
        } catch (\Exception $e) {
            return $this->exception($e);
        }
    }

    public function getUserGroupList()
    {
        $data = $this->jsonData;
        try {
            $wxUser = WXUser::where('cl_OpenId', $data->m_openId)->first();
            if (empty($wxUser))
                return $this->error(JErrorCode::WX_USER_INFO_NOT_FOUND_ERROR);

            $user = $wxUser->user;

            $groupMember = GameGroupMember::valid()->where('cl_UserId', $user->user_id)->first();
            if (empty($groupMember))
                return $this->error(JErrorCode::CUSTOM_SELECT_NOT_FOUND);

            $group = $groupMember->group;

            $this->result_param['id'] = $group->cl_Id;
            $this->result_param['groupName'] = $group->cl_Name;
            $this->result_param['master'] = $group->cl_Master;

            $this->result_param['userId'] = $groupMember->cl_UserId;
            $this->result_param['level'] = $groupMember->cl_Level;

            foreach ($group->members()->valid()->orderby('cl_Level', 'desc')->get() as $item) {
                $result_item = $this->std();

                $gameinfo = $item->user->gameinfo;

                $result_item->userId = $item->cl_UserId;
                $result_item->alias = $item->user->alias;
                $result_item->phone = $item->user->mobile_phone;
                $result_item->gameNickName = null == $gameinfo ? '' : $gameinfo->cl_NickName;
                $result_item->wxHeadUrl = $item->user->wxuser->headimgurl;
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

            $groupMember = GameGroupMember::valid()->where('cl_UserId', $user->user_id)->first();
            if (empty($groupMember))
                return $this->error(JErrorCode::CUSTOM_SELECT_NOT_FOUND);

            $group = $groupMember->group;

            if (empty($group->cl_InviteTime) || !$group->validInviteTime()) {
                $group->cl_InviteTime = TimeUtil::getChinaTime();
                $group->cl_InviteCode = $group->cl_Id . Comm::make_rand(3);
                $group->save();
            }

            $this->result_param['code'] = $group->cl_InviteCode;

            return $this->result();
        } catch (\Exception $e) {
            return $this->exception($e);
        }
    }

    public function getGroupQrCode()
    {
        $data = $this->jsonData;
        try {
            $wxUser = WXUser::where('cl_OpenId', $data->m_openId)->first();
            if (empty($wxUser))
                return $this->error(JErrorCode::WX_USER_INFO_NOT_FOUND_ERROR);

            $user = $wxUser->user;

            $groupMember = GameGroupMember::valid()->where('cl_UserId', $user->user_id)->first();
            if (empty($groupMember))
                return $this->error(JErrorCode::CUSTOM_SELECT_NOT_FOUND);

            $group = $groupMember->group;

            $scanCode = ScanCode::valid()->where('cl_Action', ScanCode::SCAN_CODE_ACTION_AGC)->where('cl_Flag', $group->cl_Id)->first();

            if (empty($group->cl_InviteTime) || !$group->validInviteTime()) {
                $group->cl_InviteTime = TimeUtil::getChinaTime();
                $group->cl_InviteCode = $group->cl_Id . Comm::make_rand(3);
                $group->save();
            }

            $codeData = ['inviteCode' => $group->cl_InviteCode];

            if (!empty($scanCode)) {
                $scanCode->cl_Data = json_encode($codeData);
                $scanCode->save();
                $codeStr = ScanCode::SCAN_CODE_ACTION_AGC . '@:sn=' . $scanCode->cl_Code;
            } else {
                $code = Comm::getGuid();
                $codeStr = ScanCode::SCAN_CODE_ACTION_AGC . '@:sn=' . $code;

                $scanCodeData['cl_Flag'] = $group->cl_Id;
                $scanCodeData['cl_Code'] = $code;
                $scanCodeData['cl_Action'] = ScanCode::SCAN_CODE_ACTION_AGC;
                $scanCodeData['cl_Data'] = json_encode($codeData);
                $scanCodeData['cl_CreateTime'] = TimeUtil::getChinaTime();
                $scanCodeData['cl_ValidTime'] = TimeUtil::increaseTime($scanCodeData['cl_CreateTime'], 10, 'year');//有效期
                ScanCode::create($scanCodeData);
            }

            $this->result_param['qrcode'] = $codeStr;

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

            $groupMember = GameGroupMember::valid()->where('cl_UserId', $user->user_id)->first();
            if (!empty($groupMember))
                return $this->errori('已加入联盟');

            $group = GameGroup::where('cl_InviteCode', $data->inviteCode)->first();
            if (empty($group))
                return $this->errori('邀请码无效');

            if (!$group->cl_IsInviteCodeJoin)
                return $this->errori('联盟已关闭邀请码加入');

            $retMsg = '';
            if (!$group->validInviteTime($retMsg)) {   //邀请码时间校验
                return $this->error(JErrorCode::ERROR, $retMsg);
            }

            $retMsg = '';
            if ($group->isMemberLimit($retMsg))
                return $this->error(JErrorCode::ERROR, $retMsg);

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

    public function shareJoinGroup()
    {
        $data = $this->jsonData;
        try {
            if (!isset($data->shareUserId) || !isset($data->groupId))
                return $this->error(JErrorCode::LACK_PARAM_ERROR);

            $wxUser = WXUser::where('cl_OpenId', $data->m_openId)->first();
            if (empty($wxUser))
                return $this->error(JErrorCode::WX_USER_INFO_NOT_FOUND_ERROR);

            $user = $wxUser->user;

            $group = GameGroup::find($data->groupId);
            if (empty($group))
                return $this->error(JErrorCode::CUSTOM_SELECT_NOT_FOUND);

            if (!$group->cl_IsShareJoin)
                return $this->errori('联盟已关闭分享加入');

            $retMsg = '';
            if ($group->isMemberLimit($retMsg))
                return $this->error(JErrorCode::ERROR, $retMsg);

            $groupMember = GameGroupMember::valid()->where('cl_UserId', $user->user_id)->first();
            if (!empty($groupMember))
                return $this->errori('已加入联盟');

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

    /**
     * 发送遭受集结通知
     */
    public function sendJiJieNotice()
    {
        $data = $this->jsonData;
        try {
            if (!isset($data->toUserId) || !isset($data->type))   //type 0扣款touser  1扣款senduser
                return $this->error(JErrorCode::LACK_PARAM_ERROR);

            $wxUser = WXUser::where('cl_OpenId', $data->m_openId)->first();
            if (empty($wxUser))
                return $this->error(JErrorCode::WX_USER_INFO_NOT_FOUND_ERROR);

            $user = $wxUser->user;

            $groupMember = GameGroupMember::valid()->where('cl_UserId', $user->user_id)->first();
            if (empty($groupMember))
                return $this->error(JErrorCode::CUSTOM_SELECT_NOT_FOUND);

            $toUserGroupMember = GameGroupMember::valid()->where('cl_UserId', $data->toUserId)->first();
            if (empty($toUserGroupMember))
                return $this->error(JErrorCode::CUSTOM_SELECT_NOT_FOUND);

            if ($groupMember->cl_GroupId != $toUserGroupMember->cl_GroupId)
                return $this->errori('非正常操作，同一个联盟下才可以通知');

            $toUserSystem = UserSystem::where('cl_UserId', $data->toUserId)->first();
            if (!empty($toUserSystem)) {
                if (!$toUserSystem->cl_IsOpenJijie)
                    return $this->errori('该成员已关闭接收通知');

                $ziduan = 'cl_IsOpenJijie' . $groupMember->cl_Level;
                if (!$toUserSystem->$ziduan)
                    return $this->errori('该成员限制通知阶级');
            } else {
                if ($groupMember->cl_Level == 1)
                    return $this->errori('暂无权限发送通知');
            }

            $msgList = configCustom('userNoticeMsgList');
            $msgPriceList = configCustom(CUSTOM_USER_NOTICE_MSG_PRICE_LIST_DEFINE);
            $msgPrice = $msgPriceList[1];
            $toUser = User::find($data->toUserId);
            if (empty($toUser))
                return $this->error(JErrorCode::CUSTOM_SELECT_NOT_FOUND);

            $extCode = 0;
            if ($toUser->user_money < $msgPrice && $user->user_money < $msgPrice)
                $extCode = 1002;
            else if ($toUser->user_money < $msgPrice)
                $extCode = 1001;

            if ($data->type == 1 && $extCode != 1002)
                $extCode = 0;

            if ($extCode > 0) {
                $this->result_param['extCode'] = $extCode;
                return $this->result();
            }

            $msg = $msgList[1];
            $serviceResult = RunService::voice($toUser->mobile_phone, $msg, $toUser->user_id, $resultMsg);
            if (!$serviceResult) {
                return $this->errori('通知失败，服务异常');
            }

            if ($data->type == 0) {
                UserNoticeRecord::add($toUser->user_id, '遭受集结通知', $toUser->mobile_phone, 1, $user->user_id, '');

                $toUser->decrement('user_money', $msgPrice);
            } else if ($data->type == 1) {
                UserNoticeRecord::add($toUser->user_id, '遭受集结通知(盟友代扣款)', $toUser->mobile_phone, 1, $user->user_id, '发送人代扣款');

                $user->decrement('user_money', $msgPrice);
            } else
                return $this->error();


            return $this->success();
        } catch (\Exception $e) {
            return $this->exception($e);
        }
    }

    public function updateGroupLevel()
    {
        $data = $this->jsonData;
        try {
            if (!isset($data->memberUserId) || !isset($data->level))
                return $this->error(JErrorCode::LACK_PARAM_ERROR);

            $wxUser = WXUser::where('cl_OpenId', $data->m_openId)->first();
            if (empty($wxUser))
                return $this->error(JErrorCode::WX_USER_INFO_NOT_FOUND_ERROR);

            $user = $wxUser->user;

            $groupMember = GameGroupMember::valid()->where('cl_UserId', $data->memberUserId)->first();
            if (empty($groupMember))
                return $this->error(JErrorCode::CUSTOM_SELECT_NOT_FOUND);

            $curGroupMember = GameGroupMember::valid()->where('cl_UserId', $user->user_id)->first();
            if (empty($curGroupMember) || $curGroupMember->cl_GroupId != $groupMember->cl_GroupId || $curGroupMember->cl_Level < 4) //允许4 5 级修改成员阶级
                return $this->errori('无权限！');

            if (!in_array($data->level, [1, 2, 3, 4, 5]))
                return $this->errori('无效阶级！');

            $groupMember->cl_Level = $data->level;
            $groupMember->save();

            return $this->success();
        } catch (\Exception $e) {
            return $this->exception($e);
        }
    }

    public function deleteGroupMember()
    {
        $data = $this->jsonData;
        try {
            if (!isset($data->memberUserId))
                return $this->error(JErrorCode::LACK_PARAM_ERROR);

            $wxUser = WXUser::where('cl_OpenId', $data->m_openId)->first();
            if (empty($wxUser))
                return $this->error(JErrorCode::WX_USER_INFO_NOT_FOUND_ERROR);

            $user = $wxUser->user;

            $groupMember = GameGroupMember::valid()->where('cl_UserId', $data->memberUserId)->first();
            if (empty($groupMember))
                return $this->error(JErrorCode::CUSTOM_SELECT_NOT_FOUND);

            $curGroupMember = GameGroupMember::valid()->where('cl_UserId', $user->user_id)->first();
            if (empty($curGroupMember) || $curGroupMember->cl_GroupId != $groupMember->cl_GroupId || $curGroupMember->cl_Level < 4) //允许4 5 级操作
                return $this->errori('无权限！');

            $groupMember->cl_Status = 0;
            $groupMember->save();

            return $this->success();
        } catch (\Exception $e) {
            return $this->exception($e);
        }
    }

    public function abdicateGroupMaster()
    {
        $data = $this->jsonData;
        try {
            if (!isset($data->memberUserId))
                return $this->error(JErrorCode::LACK_PARAM_ERROR);

            $wxUser = WXUser::where('cl_OpenId', $data->m_openId)->first();
            if (empty($wxUser))
                return $this->error(JErrorCode::WX_USER_INFO_NOT_FOUND_ERROR);

            $user = $wxUser->user;

            $groupMember = GameGroupMember::valid()->where('cl_UserId', $data->memberUserId)->first();
            if (empty($groupMember))
                return $this->error(JErrorCode::CUSTOM_SELECT_NOT_FOUND);

            $curGroupMember = GameGroupMember::valid()->where('cl_UserId', $user->user_id)->first();
            Tool::writeLog(json_encode($groupMember));
            Tool::writeLog(json_encode($curGroupMember));
            if (empty($curGroupMember) || $curGroupMember->cl_GroupId != $groupMember->cl_GroupId || $curGroupMember->cl_Level != 5) //允许5级操作
                return $this->errori('无权限！');

            $group = $groupMember->group;
            $group->cl_Master = $data->memberUserId;
            $group->save();

            $groupMember->cl_Level = 5;
            $groupMember->save();

            $curGroupMember->cl_Level = 4;
            $curGroupMember->save();

            return $this->success();
        } catch (\Exception $e) {
            return $this->exception($e);
        }
    }

    public function leaveGroup()
    {
        $data = $this->jsonData;
        try {
            $wxUser = WXUser::where('cl_OpenId', $data->m_openId)->first();
            if (empty($wxUser))
                return $this->error(JErrorCode::WX_USER_INFO_NOT_FOUND_ERROR);

            $user = $wxUser->user;

            $curGroupMember = GameGroupMember::valid()->where('cl_UserId', $user->user_id)->first();
            if ((empty($curGroupMember) || $curGroupMember->cl_Level == 5) && $curGroupMember->group->members()->valid()->count() > 1)
                return $this->errori('请先让位给其他成员！');

            $curGroupMember->cl_Status = 0;
            $curGroupMember->save();

            return $this->success();
        } catch (\Exception $e) {
            return $this->exception($e);
        }
    }

}