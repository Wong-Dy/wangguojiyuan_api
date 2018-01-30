<?php
/**
 * 公共服务类接口入口
 * User: wdy
 * Date: 2017/1/27
 * Time: 11:10
 */

namespace App\Service;


use App\Models\ServiceLog;
use App\Util\Tool;
use Exception;

class RunService
{
    protected static $logPath = '/logs/service/run/';

    public static function voice($mobile, $msg, $userId = 0, &$resultMsg)
    {
        $result = SubMailService::voice($mobile, $msg);
        if (empty($result)) {
            $resultMsg = '接口调用失败';
            return false;
        }

        $remark = '';
        $status = 1;
        if ($result->status != 'success') {
            $resultMsg = 'error:' . $result->code . ',msg:' . $result->msg;
            $status = 0;
            $remark = '发送失败';
        } else {
            if ($result->money_account < 30) {
                $remark = '语音余额低于 30 元！！！';
                $status = 2;
            }

            $resultMsg = 'send_id=' . $result->send_id;
        }
        $ret = ServiceLog::add("语音通知 mobile：{$mobile}", $msg, $result, $userId, 2, $remark, $status);
        if (!$ret)
            $resultMsg = '服务日志记录失败';

        return $result->status == 'success';
    }

    public static function message($mobile, $msg, $userId = 0, &$resultMsg)
    {
        $result = SubMailService::message($mobile, $msg);
        if (empty($result)) {
            $resultMsg = '接口调用失败';
            return false;
        }

        $remark = '';
        $status = 1;
        if (!$result->status == 'success') {
            $resultMsg = 'error:' . $result->code . ',msg:' . $result->msg;
            $status = 0;
            $remark = '发送失败';
        } else {
            if ($result->sms_credits < 100) {
                $remark = '短信条数低于 100 ！！！';
                $status = 2;
            }

            $resultMsg = 'send_id=' . $result->send_id;
        }
        $ret = ServiceLog::add("短信通知 mobile：{$mobile}", $msg, $result, $userId, 1, $remark, $status);
        if (!$ret)
            $resultMsg = '服务日志记录失败';

        return $result->status == 'success';
    }

}