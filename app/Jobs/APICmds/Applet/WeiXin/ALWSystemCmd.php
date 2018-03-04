<?php
/**
 * User: Administrator
 * Date: 2016/12/15
 * Time: 15:34
 */

namespace App\Jobs\APICmds\Applet\WeiXin;

use App\JsonParse\JErrorCode;
use App\Models\AppFeedback;
use App\Models\GameGroup;
use App\Models\GameGroupMember;
use App\Models\ScanCode;
use App\Models\WXUser;
use App\Util\TimeUtil;

class ALWSystemCmd extends BaseCmd
{
    public function __construct($jsonData)
    {
        parent::__construct($jsonData);
        $this->logPath .= 'system/';
    }

    public function addFeedBack()
    {
        $data = $this->jsonData;
        try {
            if (!isset($data->appName) || !isset($data->score) || !isset($data->content))
                return $this->error(JErrorCode::LACK_PARAM_ERROR);

            $wxUser = WXUser::where('cl_OpenId', $data->m_openId)->first();
            if (empty($wxUser))
                return $this->error(JErrorCode::WX_USER_INFO_NOT_FOUND_ERROR);

            $user = $wxUser->user;

            $model['cl_Account'] = $user->user_name;
            $model['cl_AppName'] = $data->appName;
            $model['cl_Phone'] = $user->mobile_phone;
            $model['cl_Score'] = $data->score;
            $model['cl_Content'] = $data->content;
            $model["cl_CreateTime"] = TimeUtil::getChinaTime();

            if (!$ret_gid = AppFeedback::insertGetId($model))
                return $this->errori('添加反馈失败');

            return $this->success();
        } catch (\Exception $e) {
            return $this->exception($e);
        }
    }

    public function scanCode()
    {
        $data = $this->jsonData;
        try {
            if (!isset($data->code))
                return $this->error(JErrorCode::LACK_PARAM_ERROR);

            if (empty($data->code))
                return $this->error(JErrorCode::INVALID_PARAM_ERROR);

            $wxUser = WXUser::where('cl_OpenId', $data->m_openId)->first();
            if (empty($wxUser))
                return $this->error(JErrorCode::WX_USER_INFO_NOT_FOUND_ERROR);

            $user = $wxUser->user;

            if (strstr($data->code, '@:') !== false) {
                $codeArr = explode('@:', $data->code);
                if (count($codeArr) > 1 && count($dataArr = $this->parseDataStyle1($codeArr[1])) > 0) {
                    switch ($codeArr[0]) {
                        case 'AGC': //扫描联盟二维码  【AGC@:sn=123】
                            $this->result_param['Type'] = 101;

                            $scanCode = ScanCode::valid()->where('cl_Code', $dataArr['sn'])->first();
                            if (empty($scanCode))
                                return $this->errori('联盟码无效或已过期');

                            $scanData = json_decode($scanCode->cl_Data);

                            $groupMember = GameGroupMember::valid()->where('cl_UserId', $user->user_id)->first();
                            if (!empty($groupMember))
                                return $this->errori('已加入联盟');

                            $group = GameGroup::find($scanCode->cl_Flag);
                            if (empty($group))
                                return $this->errori('联盟不存在');

                            if ($scanData->inviteCode != $group->cl_InviteCode || empty($group->cl_InviteTime) || !$group->validInviteTime()) {
                                return $this->errori('邀请码过期,请扫描最新的联盟二维码');
                            }

                            $this->result_param['inviteCode'] = $scanData->inviteCode;
                            $this->result_param['groupId'] = $group->cl_Id;

                            break;
                    }
                }
            }

            return $this->result();
        } catch (\Exception $e) {
            return $this->exception($e);
        }
    }

}