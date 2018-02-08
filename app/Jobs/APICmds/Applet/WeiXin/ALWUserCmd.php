<?php
/**
 * User: Administrator
 * Date: 2016/12/15
 * Time: 15:34
 */

namespace App\Jobs\APICmds\Applet\WeiXin;


use App\JsonParse\JErrorCode;
use App\Models\User;
use App\Models\UserAccount;
use App\Models\UserGameInfo;
use App\Models\UserNoticeRecord;
use App\Models\UserNoticeTask;
use App\Models\UserSystem;
use App\Models\WXUser;
use App\Service\RunService;
use App\Util\CGlobal;
use App\Util\Comm;
use App\Util\Crypt\WeiXin\WXBizDataCrypt;
use App\Util\HttpUtil;
use App\Util\TimeUtil;
use App\Util\Tool;
use App\Util\ValidateUtil;
use Cache;

class ALWUserCmd extends BaseCmd
{
    public function __construct($jsonData)
    {
        parent::__construct($jsonData);
        $this->logPath .= 'user/';
    }

    public function login()
    {
        $data = $this->jsonData;
        try {
            if (!isset($data->encryptedData) || !isset($data->iv))
                return $this->error(JErrorCode::LACK_PARAM_ERROR);

            $wxCrypt = new WXBizDataCrypt($data->m_appid, $data->m_sessionKey);
            $ret = $wxCrypt->decryptData($data->encryptedData, urldecode($data->iv), $resultData);
            $wxUserData = json_decode($resultData);
            unset($resultData);

//            $wxUserData = {"openId":"ogcP90I3-ebXzx1OCiW0FJziYQ5k","nickName":"BangBangBang","gender":1,"language":"zh_CN","city":"Linyi","province":"Shandong","country":"China",
//"avatarUrl":"https:\/\/wx.qlogo.cn\/mmopen\/vi_32\/DYAIOgq83eqRWY7g8yMUgB7Uc2g2mTCaCw7ceuWhIczYP8aVHp9EWlAh8icXR6ic8os354AkoVzo2DWDCLu8Sibdw\/0",
//"watermark":{"timestamp":1514171823,"appid":"wxd7926aea32a0491f"}}
            if (empty($wxUserData))
                return $this->error(JErrorCode::WX_SESSIONID_NOT_VALID_ERROR);

            $wxUserInfo = WXUser::where('cl_OpenId', $wxUserData->openId)->first();
            if (empty($wxUserInfo) || !$wxUserInfo->isBindUser()) {
                $wxUserInfoArr['avatarUrl'] = $wxUserData->avatarUrl;
                $wxUserInfoArr['province'] = $wxUserData->province;
                $wxUserInfoArr['city'] = $wxUserData->city;
                $wxUserInfoArr['gender'] = $wxUserData->gender;
                $wxUserInfoArr['nickName'] = $wxUserData->nickName;
                $wxUserInfoArr['country'] = $wxUserData->country;

                if (isset($wxUserData->unionId)) {
                    $wxUserInfoArr['unionId'] = $wxUserData->unionId;
                }
                $ret = WXUser::bindWxXcxUser($wxUserData->openId, $wxUserInfoArr, $resultData);
                $userId = $resultData['userId'];
                if ($userId < 100) {
                    $msgPriceList = configCustom(CUSTOM_USER_NOTICE_MSG_PRICE_LIST_DEFINE);
                    $amount = $msgPriceList[0] * 5;
                    User::find($userId)->increment('user_money', $amount); //赠送5条开盾通知
                    UserAccount::recharge($userId, $amount, '首次登录赠送', 1);
                }

            } else {
                $userId = $wxUserInfo->ecuid;
            }
            $user = User::find($userId);
            if (empty($user))
                return $this->error(JErrorCode::CUSTOM_USER_NOT_FOUND);

            $this->result_param['isPhone'] = empty($user->mobile_phone) ? 0 : 1;
            $this->result_param['userId'] = $user->user_id;
            $this->result_param['account'] = $user->user_name;
            $this->result_param['money'] = $user->user_money;
            $this->result_param['name'] = $user->alias;
            $this->result_param['phone'] = $user->mobile_phone;
            $this->result_param['email'] = $user->email;
            $this->result_param['imgUrl'] = $user->getHeadImg();

            return $this->result();
        } catch (\Exception $e) {
            return $this->exception($e);
        }
    }

    public function sendAuthCode()
    {
        $data = $this->jsonData;
        try {
            if (!isset($data->phone))
                return $this->error(JErrorCode::LACK_PARAM_ERROR);

            if (!ValidateUtil::phoneVerify($data->phone))
                return $this->error(JErrorCode::CUSTOM_PHONE_FORMAT_ERROR);

            $wxUser = WXUser::where('cl_OpenId', $data->m_openId)->first();
            if (empty($wxUser))
                return $this->error(JErrorCode::WX_USER_INFO_NOT_FOUND_ERROR);

            $user = $wxUser->user;

            $redisCache = REDIS_USER_AUTH_CODE_CACHE_WX_XCX . $data->phone;
            if (Cache::has($redisCache) && !empty($oldCode = Cache::get($redisCache))) {
                $this->result_param['code'] = $oldCode;
                return $this->result();
            }

            $code = rand(1111, 9999);
            $captcha = configCustom('captcha');
            $content = str_replace('@Code', $code, $captcha);
//            $url = CUSTOM_SMS_URL . "?Mobile=" . $data->phone . "&Content=" . urlencode($content);

            $resultMsg = '';
            $ret = RunService::message($data->phone, $content, $user->user_id, $resultMsg);

//            $html = HttpUtil::sendGet($url);
//            if (empty($html))
//                return $this->error(JErrorCode::INTERFACE_REQUEST_PHONE_MSG_ERROR);

            if (!$ret)
                return $this->error(JErrorCode::INTERFACE_REQUEST_PHONE_MSG_ERROR);

            Cache::put($redisCache, $code, 5);

            $this->result_param['code'] = $code;
            return $this->result();
        } catch (\Exception $e) {
            return $this->exception($e);
        }
    }

    public function verifyAuthCode()
    {
        $data = $this->jsonData;
        try {
            if (!isset($data->code) || !isset($data->phone))
                return $this->error(JErrorCode::LACK_PARAM_ERROR);

            $redisCache = REDIS_USER_AUTH_CODE_CACHE_WX_XCX . $data->phone;
            if (Cache::has($redisCache) && !empty($oldCode = Cache::get($redisCache))) {
                if ($data->code != $oldCode)
                    return $this->error(JErrorCode::OTHER_ERROR, '验证码错误');

            } else
                return $this->error(JErrorCode::OTHER_ERROR, '验证码失效，请重新发送');

            return $this->success();
        } catch (\Exception $e) {
            return $this->exception($e);
        }
    }

    public function bindPhone()
    {
        $data = $this->jsonData;
        try {
            if (!isset($data->code) || !isset($data->phone))
                return $this->error(JErrorCode::LACK_PARAM_ERROR);

            $redisCache = REDIS_USER_AUTH_CODE_CACHE_WX_XCX . $data->phone;
            if (Cache::has($redisCache) && !empty($oldCode = Cache::get($redisCache))) {
                if ($data->code != $oldCode)
                    return $this->error(JErrorCode::OTHER_ERROR, '验证码错误');
            } else
                return $this->error(JErrorCode::OTHER_ERROR, '验证码失效，请重新发送');

            $wxUserInfo = WXUser::where('cl_OpenId', $data->m_openId)->first();
            if (empty($wxUserInfo))
                return $this->error(JErrorCode::WX_USER_INFO_NOT_FOUND_ERROR);

            $phoneUser = User::where('mobile_phone', $data->phone)->first();
            if (!empty($phoneUser)) {   //手机号码用户已存在

                $phoneWxUser = WXUser::where('ecuid', $phoneUser->user_id)->where('cl_Type', 2)->first();
                if (!empty($phoneWxUser) && $phoneWxUser->uid == $wxUserInfo->uid)
                    return $this->success();

                //查询手机号码是否被使用
                if (!empty($phoneWxUser) && $phoneWxUser->uid != $wxUserInfo->uid)
                    return $this->error(JErrorCode::CUSTOM_USER_PHONE_IS_REGISTER);

                $wxUserInfo->ecuid = $phoneUser->user_id;
                if (!$wxUserInfo->save())
                    return $this->error(JErrorCode::OTHER_ERROR, '绑定手机失败，请重试');
            } else {
                $user = $wxUserInfo->user;
                $user->user_name = $data->phone;
                $user->mobile_phone = $data->phone;
                if (!$user->save())
                    return $this->error(JErrorCode::OTHER_ERROR, '绑定手机失败，请重试');
            }

            return $this->success();
        } catch (\Exception $e) {
            return $this->exception($e);
        }
    }

    public function wXbindPhone()
    {
        $data = $this->jsonData;
        try {
            if (!isset($data->encryptedData) || !isset($data->iv))
                return $this->error(JErrorCode::LACK_PARAM_ERROR);

            $wxCrypt = new WXBizDataCrypt($data->m_appid, $data->m_sessionKey);
            $wxCrypt->decryptData($data->encryptedData, urldecode($data->iv), $resultData);
            $wxUserData = json_decode($resultData);
            unset($resultData);

            if (empty($wxUserData))
                return $this->error(JErrorCode::WX_SESSIONID_NOT_VALID_ERROR);

            $wxUserInfo = WXUser::where('cl_OpenId', $data->m_openId)->first();
            if (empty($wxUserInfo))
                return $this->error(JErrorCode::CUSTOM_USER_NOT_FOUND);


            $phoneUser = User::where('mobile_phone', $wxUserData->purePhoneNumber)->first();
            if (!empty($phoneUser)) {

                $phoneWxUser = WXUser::where('ecuid', $phoneUser->user_id)->where('cl_Type', 2)->first();
                if (!empty($phoneWxUser) && $phoneWxUser->uid == $wxUserInfo->uid)
                    return $this->success();

                //查询手机号码是否被使用
                if (!empty($phoneWxUser) && $phoneWxUser->uid != $wxUserInfo->uid)
                    return $this->error(JErrorCode::CUSTOM_USER_PHONE_IS_REGISTER);

                $wxUserInfo->ecuid = $phoneUser->user_id;
                if (!$wxUserInfo->save())
                    return $this->error(JErrorCode::OTHER_ERROR, '绑定手机失败，请重试');
            } else {
                $user = $wxUserInfo->user;
                $user->user_name = $wxUserData->purePhoneNumber;
                $user->mobile_phone = $wxUserData->purePhoneNumber;
                if (!$user->save())
                    return $this->error(JErrorCode::OTHER_ERROR, '绑定手机失败，请重试');
            }


            $this->result_param['phone'] = $wxUserData->purePhoneNumber;
            return $this->result();
        } catch (\Exception $e) {
            return $this->exception($e);
        }
    }

    public function modifyUserNoticeTask()
    {
        $data = $this->jsonData;
        try {
            if (!isset($data->minute) || !isset($data->type))
                return $this->error(JErrorCode::LACK_PARAM_ERROR);

            $wxUser = WXUser::where('cl_OpenId', $data->m_openId)->first();
            if (empty($wxUser))
                return $this->error(JErrorCode::WX_USER_INFO_NOT_FOUND_ERROR);

            $user = $wxUser->user;
            if (empty($user->mobile_phone))
                return $this->errori('请先绑定通知手机号');

            $msgPriceList = configCustom('userNoticeMsgPriceList');
            $msgPrice = isset($msgPriceList[$data->type]) ? $msgPriceList[$data->type] : 0.12;
            if ($user->user_money < $msgPrice)
                return $this->errori('余额不足,请先充值!');


            UserNoticeTask::where('cl_UserId', $user->user_id)->where('cl_Type', $data->type)->update(['cl_Status' => 2]);

            $userNoticeTask = UserNoticeTask::where('cl_UserId', $user->user_id)->where('cl_Type', $data->type)->where('cl_Minute', $data->minute)->first();
            if (empty($userNoticeTask)) {
                $task['cl_CreateTime'] = TimeUtil::getChinaTime();
                $task['cl_UserId'] = $user->user_id;
                $task['cl_Phone'] = $user->mobile_phone;
                $task['cl_Type'] = $data->type;
                $task['cl_NoticeTime'] = TimeUtil::increaseTime($task['cl_CreateTime'], $data->minute, 'minute');
                $task['cl_Minute'] = $data->minute;
                $ret = UserNoticeTask::create($task);
            } else {
                $userNoticeTask->cl_NoticeTime = TimeUtil::increaseTime(TimeUtil::getChinaTime(), $data->minute, 'minute');
                $userNoticeTask->cl_Minute = $data->minute;
                $userNoticeTask->cl_Status = 0;
                $ret = $userNoticeTask->save();
            }

            if (!$ret)
                return $this->error(JErrorCode::CUSTOM_UPDATE_ERROR);

            return $this->success();
        } catch (\Exception $e) {
            return $this->exception($e);
        }
    }

    public function cancelUserNoticeTask()
    {
        $data = $this->jsonData;
        try {
            if (!isset($data->type))
                return $this->error(JErrorCode::LACK_PARAM_ERROR);

            $wxUser = WXUser::where('cl_OpenId', $data->m_openId)->first();
            if (empty($wxUser))
                return $this->error(JErrorCode::WX_USER_INFO_NOT_FOUND_ERROR);

            $user = $wxUser->user;
            if (empty($user->mobile_phone))
                return $this->errori('请先绑定通知手机号');

            $ret = UserNoticeTask::where('cl_UserId', $user->user_id)->where('cl_Type', $data->type)->update(['cl_Status' => 2]);

            return $this->success();
        } catch (\Exception $e) {
            return $this->exception($e);
        }
    }

    public function getUserNoticeRecord()
    {
        $data = $this->jsonData;
        try {
            if (!isset($data->m_openId))
                return $this->error(JErrorCode::LACK_PARAM_ERROR);

            $model = WXUser::where('cl_OpenId', $data->m_openId)->first();

            $dataList = UserNoticeRecord::where(['cl_UserId' => $model->ecuid])
                ->orderby('cl_NoticeTime', 'desc')
                ->page(empty($data->pageIndex) ? 1 : $data->pageIndex)
                ->paginate(empty($data->pageSize) ? CGlobal::PAGE_SIZE : $data->pageSize);

            $msgList = configCustom('userNoticeMsgList');
            $msgPriceList = configCustom(CUSTOM_USER_NOTICE_MSG_PRICE_LIST_DEFINE);
            foreach ($dataList as $item) {
                $result_item = $this->std();

                $result_item->type = $item->cl_Type;
                $result_item->typeDesc = $item->getTypeDesc();
                $result_item->noticeTime = TimeUtil::parseTime($item->cl_NoticeTime);
                $result_item->phone = $item->cl_Phone;
                $result_item->remark = $item->cl_Remark;
                $result_item->title = $item->cl_Title;
                $result_item->price = $msgPriceList[$item->cl_Type];

                $this->result_list[] = $result_item;
            }
            return $this->result();
        } catch (\Exception $e) {
            return $this->exception($e);
        }
    }

    public function getUserNoticeTask()
    {
        $data = $this->jsonData;
        try {
            if (!isset($data->type))
                return $this->error(JErrorCode::LACK_PARAM_ERROR);

            $wxUser = WXUser::where('cl_OpenId', $data->m_openId)->first();
            if (empty($wxUser))
                return $this->error(JErrorCode::WX_USER_INFO_NOT_FOUND_ERROR);

            $userNoticeTask = UserNoticeTask::where('cl_UserId', $wxUser->user->user_id)->where('cl_Type', $data->type)->where('cl_Status', 0)->first();
            if (empty($userNoticeTask))
                return $this->error(JErrorCode::CUSTOM_SELECT_NOT_FOUND);

            $noticeTime = TimeUtil::parseTimestamp($userNoticeTask->cl_NoticeTime);
            $curTime = TimeUtil::parseTimestamp(TimeUtil::getChinaTime());
            if ($curTime > $noticeTime)
                $this->result_param['minute'] = 0;
            else
                $this->result_param['minute'] = round(($noticeTime - $curTime) / 60);

            $this->result_param['totalMinute'] = $userNoticeTask->cl_Minute;

            return $this->result();
        } catch (\Exception $e) {
            return $this->exception($e);
        }
    }

    public function modifySystem()
    {
        $data = $this->jsonData;
        try {
            if (!isset($data->ddAheadNotice) || !isset($data->maintenanceAhead))
                return $this->error(JErrorCode::LACK_PARAM_ERROR);

            if (empty($data->ddAheadNotice))
                $data->ddAheadNotice = 10;
            if (empty($data->maintenanceAhead))
                $data->maintenanceAhead = 10;

            if ($data->ddAheadNotice > 30 || $data->maintenanceAhead > 30)
                return $this->errori('最大提前30分钟');

            $wxUser = WXUser::where('cl_OpenId', $data->m_openId)->first();
            if (empty($wxUser))
                return $this->error(JErrorCode::WX_USER_INFO_NOT_FOUND_ERROR);

            $user = $wxUser->user;

            $userSystem = UserSystem::where('cl_UserId', $user->user_id)->first();
            if (empty($userSystem)) {
                $userSystemData['cl_UserId'] = $user->user_id;
                $userSystemData['cl_ddAheadNotice'] = $data->ddAheadNotice;
                $userSystemData['cl_MaintenanceAhead'] = $data->maintenanceAhead;
                $userSystemData['cl_CreateTime'] = TimeUtil::getChinaTime();
                $userSystemData['cl_UpdateTime'] = $userSystemData['cl_CreateTime'];
                $ret = UserSystem::create($userSystemData);
            } else {
                $userSystem->cl_ddAheadNotice = $data->ddAheadNotice;
                $userSystem->cl_MaintenanceAhead = $data->maintenanceAhead;
                $userSystem->cl_UpdateTime = TimeUtil::getChinaTime();
                $ret = $userSystem->save();
            }

            if (!$ret)
                return $this->error(JErrorCode::CUSTOM_UPDATE_ERROR);

            return $this->success();
        } catch (\Exception $e) {
            return $this->exception($e);
        }
    }

    public function getUserSystem()
    {
        $data = $this->jsonData;
        try {

            $wxUser = WXUser::where('cl_OpenId', $data->m_openId)->first();
            if (empty($wxUser))
                return $this->error(JErrorCode::WX_USER_INFO_NOT_FOUND_ERROR);

            $user = $wxUser->user;

            $userSystem = UserSystem::where('cl_UserId', $user->user_id)->first();

            $this->result_param['phone'] = $user->mobile_phone;
            $this->result_param['ddAheadNotice'] = empty($userSystem) ? '' : $userSystem->cl_ddAheadNotice;
            $this->result_param['maintenanceAhead'] = empty($userSystem) ? '' : $userSystem->cl_MaintenanceAhead;

            return $this->result();
        } catch (\Exception $e) {
            return $this->exception($e);
        }
    }

    public function modifySetting()
    {
        $data = $this->jsonData;
        try {
//            if (!isset($data->openWeihu) || !isset($data->openJijie))
//                return $this->error(JErrorCode::LACK_PARAM_ERROR);

            $wxUser = WXUser::where('cl_OpenId', $data->m_openId)->first();
            if (empty($wxUser))
                return $this->error(JErrorCode::WX_USER_INFO_NOT_FOUND_ERROR);

            $user = $wxUser->user;

            if (isset($data->openWeihu))
                $userSystemData['cl_IsOpenWeihu'] = $data->openWeihu;
            if (isset($data->openJijie))
                $userSystemData['cl_IsOpenJijie'] = $data->openJijie;
            if (isset($data->openJijie5))
                $userSystemData['cl_IsOpenJijie5'] = $data->openJijie5;
            if (isset($data->openJijie4))
                $userSystemData['cl_IsOpenJijie4'] = $data->openJijie4;
            if (isset($data->openJijie3))
                $userSystemData['cl_IsOpenJijie3'] = $data->openJijie3;
            if (isset($data->openJijie2))
                $userSystemData['cl_IsOpenJijie2'] = $data->openJijie2;
            if (isset($data->openJijie1))
                $userSystemData['cl_IsOpenJijie1'] = $data->openJijie1;
            $userSystemData['cl_UpdateTime'] = TimeUtil::getChinaTime();

            $userSystem = UserSystem::where('cl_UserId', $user->user_id)->first();
            if (empty($userSystem)) {
                $userSystemData['cl_UserId'] = $user->user_id;
                $userSystemData['cl_CreateTime'] = TimeUtil::getChinaTime();

                $ret = UserSystem::create($userSystemData);
            } else {
                $ret = $userSystem->update($userSystemData);
            }

            if (!$ret)
                return $this->error(JErrorCode::CUSTOM_UPDATE_ERROR);

            return $this->success();
        } catch (\Exception $e) {
            return $this->exception($e);
        }
    }

    public function getUserSetting()
    {
        $data = $this->jsonData;
        try {

            $wxUser = WXUser::where('cl_OpenId', $data->m_openId)->first();
            if (empty($wxUser))
                return $this->error(JErrorCode::WX_USER_INFO_NOT_FOUND_ERROR);

            $user = $wxUser->user;

            $userSystem = UserSystem::where('cl_UserId', $user->user_id)->first();

            $this->result_param['openWeihu'] = empty($userSystem) ? 1 : $userSystem->cl_IsOpenWeihu;
            $this->result_param['openJijie'] = empty($userSystem) ? 1 : $userSystem->cl_IsOpenJijie;
            $this->result_param['openJijie5'] = empty($userSystem) ? 1 : $userSystem->cl_IsOpenJijie5;
            $this->result_param['openJijie4'] = empty($userSystem) ? 1 : $userSystem->cl_IsOpenJijie4;
            $this->result_param['openJijie3'] = empty($userSystem) ? 1 : $userSystem->cl_IsOpenJijie3;
            $this->result_param['openJijie2'] = empty($userSystem) ? 1 : $userSystem->cl_IsOpenJijie2;
            $this->result_param['openJijie1'] = empty($userSystem) ? 0 : $userSystem->cl_IsOpenJijie1;

            return $this->result();
        } catch (\Exception $e) {
            return $this->exception($e);
        }
    }

    public function modifyGameAccount()
    {
        $data = $this->jsonData;
        try {
//            if (!isset($data->openWeihu) || !isset($data->openJijie))
//                return $this->error(JErrorCode::LACK_PARAM_ERROR);

            $wxUser = WXUser::where('cl_OpenId', $data->m_openId)->first();
            if (empty($wxUser))
                return $this->error(JErrorCode::WX_USER_INFO_NOT_FOUND_ERROR);

            $user = $wxUser->user;

            if (isset($data->nickName))
                $modelData['cl_NickName'] = $data->nickName;

            $modelData['cl_UpdateTime'] = TimeUtil::getChinaTime();

            $userGameInfo = UserGameInfo::where('cl_UserId', $user->user_id)->first();
            if (empty($userGameInfo)) {
                $modelData['cl_UserId'] = $user->user_id;
                $modelData['cl_CreateTime'] = TimeUtil::getChinaTime();

                $ret = UserGameInfo::create($modelData);
            } else {
                $ret = $userGameInfo->update($modelData);
            }

            if (!$ret)
                return $this->error(JErrorCode::CUSTOM_UPDATE_ERROR);

            return $this->success();
        } catch (\Exception $e) {
            return $this->exception($e);
        }
    }

    public function getGameAccount()
    {
        $data = $this->jsonData;
        try {

            $wxUser = WXUser::where('cl_OpenId', $data->m_openId)->first();
            if (empty($wxUser))
                return $this->error(JErrorCode::WX_USER_INFO_NOT_FOUND_ERROR);

            $user = $wxUser->user;

            $model = UserGameInfo::where('cl_UserId', $user->user_id)->first();

            $this->result_param['nickName'] = empty($model) ? 1 : $model->cl_NickName;

            return $this->result();
        } catch (\Exception $e) {
            return $this->exception($e);
        }
    }

    public function getRechargeRecords()
    {
        $data = $this->jsonData;
        try {
            if (!isset($data->m_openId))
                return $this->error(JErrorCode::LACK_PARAM_ERROR);

            $model = WXUser::where('cl_OpenId', $data->m_openId)->first();

            $dataList = UserAccount::where(['user_id' => $model->ecuid, 'process_type' => 0])
                ->orderby('add_time', 'desc')
                ->page(empty($data->pageIndex) ? 1 : $data->pageIndex)
                ->paginate(empty($data->pageSize) ? CGlobal::PAGE_SIZE : $data->pageSize);

            foreach ($dataList as $item) {
                $result_item = $this->std();

                $result_item->userNote = $item->user_note;
                $result_item->addTime = TimeUtil::parseTimestampToDateTime($item->add_time, $format = 'Y-m-d H:i');
                $result_item->amount = $item->amount;
                $result_item->payMent = $item->cl_Payment;
                $result_item->system = $item->cl_System;
                $result_item->tradeNo = $item->cl_TradeNo;
                $result_item->isPaid = $item->is_paid;

                $this->result_list[] = $result_item;
            }
            return $this->result();
        } catch (\Exception $e) {
            return $this->exception($e);
        }
    }

    public function getUserMoney()
    {
        $data = $this->jsonData;
        try {
            $wxUser = WXUser::where('cl_OpenId', $data->m_openId)->first();
            if (empty($wxUser))
                return $this->error(JErrorCode::CUSTOM_USER_NOT_FOUND);

            $user = User::find($wxUser->ecuid);
            $this->result_param['money'] = $user->user_money;

            return $this->result();
        } catch (\Exception $e) {
            return $this->exception($e);
        }
    }
}