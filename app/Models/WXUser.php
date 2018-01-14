<?php
/**
 * 操作模型
 * User: Administrator
 * Date: 2017/10/17
 * Time: 16:11
 */

namespace App\Models;

use App\Util\TimeUtil;
use App\Util\Tool;
use DB;

class WXUser extends Base
{
    protected $table = 'wdy_weixin_user';
    protected $primaryKey = "uid";

    public function user()
    {
        return $this->hasOne('App\Models\User', 'user_id', 'ecuid');
    }

    public function isBindUser()
    {
        if (empty($this->ecuid))
            return 0;

        $user = $this->user;
        if (empty($user))
            return 0;

        return 1;
    }

    /**
     * 绑定微信小程序用户信息
     * @param $openId
     * @param $wxUserData
     * @return int
     */
    public static function bindWxXcxUser($openId, $wxUserData, &$resultData)
    {
        $userThirdpartyArr['user_id'] = $openId;
        $wxUserInfo = WXUser::where('cl_OpenId', $userThirdpartyArr['user_id'])->first();

        if (empty($wxUserInfo)) {  //微信信息不存在
            if (isset($wxUserData) && !empty($wxUserData)) {
                $userThirdpartyArr['avatar'] = isset($wxUserData['avatarUrl']) ? $wxUserData['avatarUrl'] : "";
                $userThirdpartyArr['province'] = isset($wxUserData['province']) ? $wxUserData['province'] : "";
                $userThirdpartyArr['city'] = isset($wxUserData['city']) ? $wxUserData['city'] : "";
                $userThirdpartyArr['gender'] = isset($wxUserData['gender']) ? ($wxUserData['gender'] == '1' ? '1' : $wxUserData['gender'] == '2' ? '0' : "0") : 0;
                $userThirdpartyArr['nick_name'] = isset($wxUserData['nickName']) ? $wxUserData['nickName'] : "";
                $userThirdpartyArr['country'] = isset($wxUserData['country']) ? $wxUserData['country'] : "";
                $userThirdpartyArr['unionId'] = isset($wxUserData['unionId']) ? $wxUserData['unionId'] : "";

                DB::beginTransaction();
                try {
                    $userData['user_name'] = User::createAccount();
                    $userData['password'] = '';
                    $userData['alias'] = $userThirdpartyArr['nick_name'];
                    $userData['real_name'] = $userThirdpartyArr['nick_name'];
                    $userData['mobile_phone'] = '';
                    $userData['headimg'] = "";
                    $userData['reg_time'] = strtotime(TimeUtil::getChinaTime());
                    $userData['froms'] = 'wxin_xcx';
                    $userId = User::insertGetId($userData);

                    $UserInfo['ecuid'] = $userId;
                    $UserInfo['cl_OpenId'] = $userThirdpartyArr['user_id'];
                    $UserInfo['fake_id'] = $userThirdpartyArr['user_id'];
                    $UserInfo['nickname'] = $userThirdpartyArr['nick_name'];
                    $UserInfo['sex'] = $userThirdpartyArr['gender'];
                    $UserInfo['country'] = $userThirdpartyArr['country'];
                    $UserInfo['createtime'] = strtotime(TimeUtil::getChinaTime());
                    $UserInfo['Province'] = $userThirdpartyArr['province'];
                    $UserInfo['city'] = $userThirdpartyArr['city'];
                    $UserInfo['headimgurl'] = $userThirdpartyArr['avatar'];
                    $UserInfo['cl_UnionId'] = $userThirdpartyArr['unionId'];
                    $UserInfo['cl_Type'] = 2;
                    WXUser::create($UserInfo);

                    DB::commit();

                    $resultData['userId'] = $userId;
                } catch (\Exception $e) {
                    Tool::writeLog($e, __FUNCTION__);
                    DB::rollBack();
                    return 99;
                }
            }
        } else {
            return 0;
        }
        return 1;
    }

}