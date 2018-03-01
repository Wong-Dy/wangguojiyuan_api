<?php
/*手持终端*/

namespace App\Http\Controllers\API\Applet;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Jobs\APICmds\Applet\WeiXin\ALWAdvertisementCmd;
use App\Jobs\APICmds\Applet\WeiXin\ALWGameCmd;
use App\Jobs\APICmds\Applet\WeiXin\ALWSystemCmd;
use App\Jobs\APICmds\Applet\WeiXin\ALWUserCmd;
use App\JsonParse\JErrorCode;
use App\JsonParse\JReturn;
use App\Models\Developer;
use App\Util\Comm;
use App\Util\EncryptUtil;
use App\Util\HttpUtil;
use App\Util\Tool;
use Input, Notification, Session, Cache, Exception;
use Illuminate\Http\Request;

class WeiXinController extends Controller
{
    protected $exceptionMsg = '服务器宝宝走神了';
    protected $logPath = '/logs/api/applet/weixin/';
    protected $appid = 'wxdb380edeef1d142a';
    protected $secret = '5654b641e0a27461d8f8ff676c53c245';

    public function __construct()
    {
    }

    public function anyAuth()
    {
        $sessionId = request('sessionId');

        $redisCache = REDIS_WX_XCX_AUTH_CACHE . $sessionId;
        if (!empty($sessionId) && Cache::has($redisCache) && !empty($wxSession = Cache::get($redisCache))) {
            $param['sessionId'] = $sessionId;
            return JReturn::result(JErrorCode::SUCCESS, $param);
        }

        $code = request('code');
        $url = "https://api.weixin.qq.com/sns/jscode2session?appid={$this->appid}&secret={$this->secret}&js_code={$code}&grant_type=authorization_code";
        $result = HttpUtil::sendGet($url);

        $result = json_decode($result);
        if (empty($result) || !isset($result->session_key)) {
            Tool::writeLog(json_encode($result), __FUNCTION__, $this->logPath);
            return JReturn::error(JErrorCode::ERROR, 'wx error');
        }

        $guid = Comm::getGuid();
        $redisCache = REDIS_WX_XCX_AUTH_CACHE . $guid;
        Cache::put($redisCache, $result, 100);

        $param['sessionId'] = $guid;

        Tool::writeLog('33333--' . json_encode($param), __FUNCTION__);
        return JReturn::result(JErrorCode::SUCCESS, $param);
    }

    public function anyIndex()
    {
        $isEncryp = 1;
        try {
            $strData = request('strData');
            $sessionId = request('sessionId');
            if (empty($sessionId)) {
                return JReturn::error(JErrorCode::WX_SESSIONID_NULL_ERROR);
            }

            $redisCache = REDIS_WX_XCX_AUTH_CACHE . $sessionId;
            if (!Cache::has($redisCache)) {
                return JReturn::error(JErrorCode::WX_SESSIONID_NOT_VALID_ERROR);
            }

            $wxSession = Cache::get($redisCache);

            if (empty($strData))
                $strData = file_get_contents("php://input");

            //$strData = '{"cmd":"login","phone":"15868153442","password":"123456"}';

            if (empty($strData))
                return JReturn::error(JErrorCode::LACK_PARAM_ERROR);

            $strData = rawurldecode($strData);
            Tool::writeLog($strData, __FUNCTION__ . '-strData', $this->logPath);

            if ($isEncryp) {
                $account = request('account');
                if (empty($account))
                    return JReturn::error(JErrorCode::LACK_PARAM_ERROR, 'account error');
                $ApiAuth = Developer::where('cl_Name', trim($account))->first();

                if (!$ApiAuth || $ApiAuth->cl_Status == 0)
                    return JReturn::error(JErrorCode::INVALID_PARAM_ERROR, 'invalid account');

                $EncryMode = new EncryptUtil();
                $ApiAuthType = $ApiAuth->cl_Type;  //解码加密方式

                $strData = $EncryMode->$ApiAuthType($strData, $sessionId, 'decode');    //解密
                Tool::writeLog($strData, 'RunCmd-strData-解密', $this->logPath);
            }

            $jsonData = [];
            try {
                $jsonData = json_decode($strData);

            } catch (Exception $e) {
                return JReturn::error(JErrorCode::EXCEPTION_ERROR, 'invalid json type');
            }

            if (!isset($jsonData->cmd)) {
                return JReturn::error(JErrorCode::LACK_PARAM_ERROR);
            }

            $jsonData->m_openId = $wxSession->openid;
            $jsonData->m_sessionKey = $wxSession->session_key;
            $jsonData->m_appid = $this->appid;

            switch ($jsonData->cmd) {
                case 'login'://登陆
                    $cmd = new ALWUserCmd($jsonData);
                    $strRet = $cmd->login();
                    break;
                case 'wXbindPhone':// 微信授权手机号
                    $cmd = new ALWUserCmd($jsonData);
                    $strRet = $cmd->wXbindPhone();
                    break;
                case 'getUserMoney':// 获取用户余额
                    $cmd = new ALWUserCmd($jsonData);
                    $strRet = $cmd->getUserMoney();
                    break;
                case 'sendAuthCode':    //发送验证码
                    $cmd = new ALWUserCmd($jsonData);
                    $strRet = $cmd->sendAuthCode();
                    break;
                case 'verifyAuthCode':  //验证码校验
                    $cmd = new ALWUserCmd($jsonData);
                    $strRet = $cmd->verifyAuthCode();
                    break;
                case 'bindPhone':  //绑定手机号码
                    $cmd = new ALWUserCmd($jsonData);
                    $strRet = $cmd->bindPhone();
                    break;
                case 'modifyUserNoticeTask':  //更改用户通知任务
                    $cmd = new ALWUserCmd($jsonData);
                    $strRet = $cmd->modifyUserNoticeTask();
                    break;
                case 'updateUserNoticeTaskTime':  //更改用户通知任务时间
                    $cmd = new ALWUserCmd($jsonData);
                    $strRet = $cmd->updateUserNoticeTaskTime();
                    break;
                case 'getUserNoticeTask':  //获取用户通知任务
                    $cmd = new ALWUserCmd($jsonData);
                    $strRet = $cmd->getUserNoticeTask();
                    break;
                case 'getUserNoticeRecord':  //获取用户通知记录
                    $cmd = new ALWUserCmd($jsonData);
                    $strRet = $cmd->getUserNoticeRecord();
                    break;
                case 'cancelUserNoticeTask':  //取消用户通知任务
                    $cmd = new ALWUserCmd($jsonData);
                    $strRet = $cmd->cancelUserNoticeTask();
                    break;
                case 'modifySystem':  //更改用户设置
                    $cmd = new ALWUserCmd($jsonData);
                    $strRet = $cmd->modifySystem();
                    break;
                case 'getUserSystem':  //获取用户系统配置
                    $cmd = new ALWUserCmd($jsonData);
                    $strRet = $cmd->getUserSystem();
                    break;
                case 'getUserSetting':  //获取用户设置
                    $cmd = new ALWUserCmd($jsonData);
                    $strRet = $cmd->getUserSetting();
                    break;
                case 'modifySetting':  //更改用户设置
                    $cmd = new ALWUserCmd($jsonData);
                    $strRet = $cmd->modifySetting();
                    break;
                case 'getRechargeRecords': // 充值明细
                    $cmd = new ALWUserCmd($jsonData);
                    $strRet = $cmd->getRechargeRecords();
                    break;
                case 'modifyGameAccount':  //更改用户游戏账户信息
                    $cmd = new ALWUserCmd($jsonData);
                    $strRet = $cmd->modifyGameAccount();
                    break;
                case 'getGameAccount':  //获取用户游戏账户信息
                    $cmd = new ALWUserCmd($jsonData);
                    $strRet = $cmd->getGameAccount();
                    break;

                case 'getAdvertisement':    //广告
                    $cmd = new ALWAdvertisementCmd($jsonData);
                    $strRet = $cmd->getAdvertisement();
                    break;

                case 'addFeedBack': //添加反馈
                    $cmd = new ALWSystemCmd($jsonData);
                    $strRet = $cmd->addFeedBack();
                    break;

                case 'createGroup': //创建游戏联盟
                    $cmd = new ALWGameCmd($jsonData);
                    $strRet = $cmd->createGroup();
                    break;
                case 'updateGroup': //更新游戏联盟
                    $cmd = new ALWGameCmd($jsonData);
                    $strRet = $cmd->updateGroup();
                    break;
                case 'updateGroupSetting': //更新游戏联盟配置
                    $cmd = new ALWGameCmd($jsonData);
                    $strRet = $cmd->updateGroupSetting();
                    break;
                case 'getUserGroup': //获取用户游戏联盟
                    $cmd = new ALWGameCmd($jsonData);
                    $strRet = $cmd->getUserGroup();
                    break;
                case 'getUserGroupList': //获取用户游戏联盟成员列表
                    $cmd = new ALWGameCmd($jsonData);
                    $strRet = $cmd->getUserGroupList();
                    break;
                case 'getGroupInviteCode': //获取游戏联盟邀请码
                    $cmd = new ALWGameCmd($jsonData);
                    $strRet = $cmd->getGroupInviteCode();
                    break;
                case 'getGroupQrCode': //获取游戏联盟二维码内容
                    $cmd = new ALWGameCmd($jsonData);
                    $strRet = $cmd->getGroupQrCode();
                    break;

                case 'joinGroup': //加入游戏联盟
                    $cmd = new ALWGameCmd($jsonData);
                    $strRet = $cmd->joinGroup();
                    break;
                case 'shareJoinGroup': //加入分享渠道游戏联盟
                    $cmd = new ALWGameCmd($jsonData);
                    $strRet = $cmd->shareJoinGroup();
                    break;
                case 'sendJiJieNotice': //发送遭受集结通知
                    $cmd = new ALWGameCmd($jsonData);
                    $strRet = $cmd->sendJiJieNotice();
                    break;
                case 'updateGroupLevel': //修改成员阶级
                    $cmd = new ALWGameCmd($jsonData);
                    $strRet = $cmd->updateGroupLevel();
                    break;
                case 'deleteGroupMember': //删除联盟成员
                    $cmd = new ALWGameCmd($jsonData);
                    $strRet = $cmd->deleteGroupMember();
                    break;
                case 'abdicateGroupMaster': //让出盟主位置
                    $cmd = new ALWGameCmd($jsonData);
                    $strRet = $cmd->abdicateGroupMaster();
                    break;
                case 'leaveGroup': //离开联盟
                    $cmd = new ALWGameCmd($jsonData);
                    $strRet = $cmd->leaveGroup();
                    break;


                default:
                    return JReturn::error(JErrorCode::INVALID_CMD_ERROR, "无效指令");
                    break;
            }

        } catch (Exception $e) {
            Tool::writeLog($e->getMessage(), 'RunCmd-异常', $this->logPath);
            return JReturn::error(JErrorCode::EXCEPTION_ERROR);
        }
        Tool::writeLog($strRet, __FUNCTION__ . '-strRet', $this->logPath);
        return $strRet;
    }

}

