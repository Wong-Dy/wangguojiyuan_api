<?php
namespace App\Http\Controllers\Callback;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserNoticeRecord;
use App\Models\UserNoticeTask;
use App\Models\UserSystem;
use App\Service\RunService;
use App\Util\TimeUtil;
use App\Util\Tool;
use Input, Notification, Session, Cache, Exception;
use Illuminate\Http\Request;

/**
 * 任务回调
 * Class TaskController
 * @package App\Http\Controllers\Callback
 */
class TaskController extends Controller
{
    protected $logPath = '/logs/controller/back/task/';

    public function getUserNotice()
    {
        try {
            $msgList = configCustom('userNoticeMsgList');
            $msgPriceList = configCustom(CUSTOM_USER_NOTICE_MSG_PRICE_LIST_DEFINE);

            $userNoticeTask = UserNoticeTask::where('cl_Status', 0)->where('cl_NoticeTime', '<', TimeUtil::increaseTime(TimeUtil::getChinaTime(), 30, 'minute', 'Y-m-d H:i:s'))->get();    //获取最大提前分钟
            Tool::writeLog('userNoticeTask count--' . count($userNoticeTask), __FUNCTION__, $this->logPath);

            foreach ($userNoticeTask as $item) {
                switch ($item->cl_Type) {
                    case 0:
                        if (isset($msgList[$item->cl_Type]) && isset($msgPriceList[$item->cl_Type])) {
                            $msg = $msgList[$item->cl_Type];
                            $msgPrice = $msgPriceList[$item->cl_Type];
                            $user = User::find($item->cl_UserId);
                            if (empty($user) || $user->cl_Status != 1 || $user->user_money < $msgPrice)
                                continue;

                            $ddAheadNotice = UserSystem::select('cl_ddAheadNotice')->where('cl_UserId', $user->user_id)->pluck('cl_ddAheadNotice')->first();
                            if (empty($ddAheadNotice))
                                $ddAheadNotice = 10;   //默认提前十分钟

                            //判断用户设置提前分钟数是否到提醒时间
                            if ($item->cl_NoticeTime > TimeUtil::increaseTime(TimeUtil::getChinaTime(), $ddAheadNotice, 'minute', 'Y-m-d H:i:s'))
                                continue;

                            Tool::writeLog(json_encode($item), __FUNCTION__, $this->logPath);

                            $resultMsg = '';
                            //发送语音通知并扣用户余额
                            $serviceResult = RunService::voice($item->cl_Phone, $msg, $user->user_id, $resultMsg);
                            if ($serviceResult) {
                                UserNoticeRecord::add($user->user_id, '艾娜希之盾到期提醒', $item->cl_Phone);

                                $item->update(['cl_Status' => 1, 'cl_Remark' => '最后发送时间=' . TimeUtil::getChinaTime() . '&resultMsg=' . $resultMsg]);
                                $user->decrement('user_money', $msgPrice);
                            } else {
                                $item->update(['cl_Remark' => $resultMsg]);
                            }
                        }
                        break;
                    case 2:
                        if (isset($msgList[$item->cl_Type]) && isset($msgPriceList[$item->cl_Type])) {
                            $msg = $msgList[$item->cl_Type];
                            $msgPrice = $msgPriceList[$item->cl_Type];


                            $modelQ = UserSystem::whereRaw(" {$item->cl_NoticeTime} > DATE_ADD(now(),INTERVAL cl_MaintenanceAhead MINUTE) ");

//                            $modelQ = User::where('cl_Status', 1)->where('user_money', '>=', $msgPrice);

                            $modelQ->chunk(50, function ($datas) use ($item, $msg, $msgPrice) {
                                foreach ($datas as $userSystemItem) {

                                    $user = User::find($userSystemItem->cl_UserId);
                                    if (empty($user) || $user->cl_Status != 1 || $user->user_money < $msgPrice)
                                        continue;

                                    $resultMsg = '';
                                    //发送语音通知并扣用户余额
                                    $serviceResult = RunService::voice($user->mobile_phone, $msg, $user->user_id, $resultMsg);
                                    if ($serviceResult) {
                                        UserNoticeRecord::add($user->user_id, '游戏维护结束提醒', $user->mobile_phone);

                                        $item->update(['cl_Status' => 1, 'cl_Time' => $item->cl_Time + 1, 'cl_Remark' => '最后发送时间=' . TimeUtil::getChinaTime() . '&resultMsg=' . $resultMsg]);
                                        $user->decrement('user_money', $msgPrice);
                                    } else {
                                        $item->update(['cl_Remark' => $resultMsg]);
                                    }
                                }
                            });


                        }
                        break;
                }
            }

            return 'success';
        } catch (\Exception $ex) {
            Tool::writeLog('异常：' . $ex->getMessage(), __FUNCTION__, $this->logPath);
            return 'fail';
        }
    }

}
