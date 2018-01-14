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

class IhuyiService
{
    protected static $logPath = '/logs/service/ihuyi/';

    protected static $voiceAccount = 'VM44417016';
    protected static $voicePassword = 'df249d882329b48b255d4447eaf70618';

    private static function send($sendData, $url = '')
    {

        return HttpUtil::sendPost($url, $sendData, 10);
    }

    private static function sendCmd($param, $url = '')
    {
        Tool::writeLog($param, __FUNCTION__, self::$logPath);
        $result = self::send($param, $url);
        Tool::writeLog($result, __FUNCTION__, self::$logPath);
        $gets = self::xml_to_array($result);
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
        $target = "http://api.vm.ihuyi.com/webservice/voice.php?method=Submit";
        $post_data = "account=" . self::$voiceAccount . "&password=" . self::$voicePassword . "&mobile=" . $mobile . "&content={$msg}";

        return self::sendCmd($post_data, $target);
    }

    private static function xml_to_array($xml)
    {
        $reg = "/<(\w+)[^>]*>([\\x00-\\xFF]*)<\\/\\1>/";
        if (preg_match_all($reg, $xml, $matches)) {
            $count = count($matches[0]);
            for ($i = 0; $i < $count; $i++) {
                $subxml = $matches[2][$i];
                $key = $matches[1][$i];
                if (preg_match($reg, $subxml)) {
                    $arr[$key] = self::xml_to_array($subxml);
                } else {
                    $arr[$key] = $subxml;
                }
            }
        }
        return $arr;
    }
}