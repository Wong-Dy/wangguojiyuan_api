<?php
/**
 * User: Administrator
 * Date: 2016/12/15
 * Time: 15:34
 */

namespace App\Jobs\APICmds\Applet\WeiXin;


use App\JsonParse\JErrorCode;
use App\Models\User;
use App\Models\UserNoticeTask;
use App\Models\UserSystem;
use App\Models\WXUser;
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
                    User::find($userId)->increment('user_money', $msgPriceList[0] * 5); //赠送5条开盾通知
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

            $redisCache = REDIS_USER_AUTH_CODE_CACHE_WX_XCX . $data->phone;
            if (Cache::has($redisCache) && !empty($oldCode = Cache::get($redisCache))) {
                $this->result_param['code'] = $oldCode;
                return $this->result();
            }

            $code = rand(1111, 9999);
            $captcha = configCustom('captcha');
            $content = str_replace('@Code', $code, $captcha);
            $url = CUSTOM_SMS_URL . "?Mobile=" . $data->phone . "&Content=" . urlencode($content);

            $html = HttpUtil::sendGet($url);
            if (empty($html))
                return $this->error(JErrorCode::INTERFACE_REQUEST_PHONE_MSG_ERROR);

            Cache::put($redisCache, $code, 10);

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
            if (!empty($phoneUser)) {
                $wxUserInfo->ecuid = $phoneUser->user_id;
                if (!$wxUserInfo->save())
                    return $this->error(JErrorCode::OTHER_ERROR, '绑定手机失败，请重试');
            }

            $user = $wxUserInfo->user;
            $user->user_name = $data->phone;
            $user->mobile_phone = $data->phone;
            if (!$user->save())
                return $this->error(JErrorCode::OTHER_ERROR, '绑定手机失败，请重试');

            return $this->success();
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
                $this->result_param['minute'] = floor(($noticeTime - $curTime) / 60);

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
            if (!isset($data->ddAheadNotice) || !isset($data->phone))
                return $this->error(JErrorCode::LACK_PARAM_ERROR);

            if (!ValidateUtil::phoneVerify($data->phone))
                return $this->error(JErrorCode::CUSTOM_PHONE_FORMAT_ERROR);

            if (empty($data->ddAheadNotice))
                $data->ddAheadNotice = 10;

            if ($data->ddAheadNotice > 30)
                return $this->errori('最多提前30分钟！');

            $wxUser = WXUser::where('cl_OpenId', $data->m_openId)->first();
            if (empty($wxUser))
                return $this->error(JErrorCode::WX_USER_INFO_NOT_FOUND_ERROR);

            $user = $wxUser->user;

            $phoneUser = User::where('mobile_phone', $data->phone)->first();
            if (!empty($phoneUser) && $phoneUser->user_id != $user->user_id) {
                return $this->errori('手机号码已有绑定');
            }

            $user->mobile_phone = $data->phone;
            if (!$user->save())
                return $this->error(JErrorCode::CUSTOM_UPDATE_ERROR);

            $userSystem = UserSystem::where('cl_UserId', $user->user_id)->first();
            if (empty($userSystem)) {
                $userSystemData['cl_UserId'] = $user->user_id;
                $userSystemData['cl_ddAheadNotice'] = $data->ddAheadNotice;
                $userSystemData['cl_CreateTime'] = TimeUtil::getChinaTime();
                $userSystemData['cl_UpdateTime'] = $userSystemData['cl_CreateTime'];
                $ret = UserSystem::create($userSystemData);
            } else {
                $userSystem->cl_ddAheadNotice = $data->ddAheadNotice;
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

            return $this->result();
        } catch (\Exception $e) {
            return $this->exception($e);
        }
    }
}