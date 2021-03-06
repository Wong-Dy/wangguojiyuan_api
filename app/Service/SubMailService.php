<?php
/**
 * 互亿无线服务接口
 * User: wdy
 * Date: 2016/2/2
 * Time: 11:10
 */

namespace App\Service;


use App\Util\HttpUtil;
use App\Util\Tool;
use Exception;

/**
 * 赛邮接口--https://www.mysubmail.com/
 * Class SubMailService
 * @package App\Service
 */
class SubMailService
{
    protected static $logPath = '/logs/service/submail/';

    private static function send($sendData, $url = '')
    {

        return HttpUtil::sendPost($url, $sendData, 10);
    }

    private static function sendCmd($param, $url = '')
    {
        Tool::writeLog($param, __FUNCTION__, self::$logPath);
        $result = self::send($param, $url);
        Tool::writeLog($result, __FUNCTION__, self::$logPath);
        $gets = json_decode($result);
        return $gets;
    }

    /**
     * 发送语音通知
     * @param $mobile
     * @param $msg
     * @return mixed
     */
    public static function voice($mobile, $msg)
    {
        $config = configCustom('service_config.submail');
        $target = "https://api.mysubmail.com/voice/send";
        $post_data = "appid=" . $config['voice_appid'] . "&to=" . $mobile . "&content=" . $msg . "&signature=" . $config['voice_appkey'];

        return self::sendCmd($post_data, $target);
        //object(stdClass)#162 (3) { ["status"]=> string(7) "success" ["send_id"]=> string(32) "1db6b69d6d2c7cc7d894da3c7355954e" ["money_account"]=> string(5) "1.860" }
    }

    public static function message($mobile, $msg)
    {
        $config = configCustom('service_config.submail');
        $target = "https://api.mysubmail.com/message/send.json";
        $post_data = "appid=" . $config['message_appid'] . "&to=" . $mobile . "&content=" . $msg . "&signature=" . $config['message_appkey'];

        return self::sendCmd($post_data, $target);
        //object(stdClass)#162 (4) { ["status"]=> string(7) "success" ["send_id"]=> string(32) "453a9f4ddc147f290ff27aaa4913e409" ["fee"]=> int(1) ["sms_credits"]=> string(2) "48" }
    }

    public static function xmessage($mobile, $project, $vars)
    {
        $config = configCustom('service_config.submail');
        $target = "https://api.mysubmail.com/message/xsend.json";
        $post_data = "appid=" . $config['message_appid'] . "&to=" . $mobile . "&project={$project}" . "&signature=" . $config['message_appkey'] . '&vars=' . json_encode($vars);

        return self::sendCmd($post_data, $target);
        //object(stdClass)#162 (4) { ["status"]=> string(7) "success" ["send_id"]=> string(32) "2edc8639544e6f2d786ef6c1011beb76" ["fee"]=> int(1) ["sms_credits"]=> string(2) "45" }
    }
}