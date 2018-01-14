<?php
namespace App\Http\Controllers\Callback;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserNoticeTask;
use App\Service\IhuyiService;
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

            $userNoticeTask = UserNoticeTask::where('cl_Status', 0)->where('cl_NoticeTime', '<', TimeUtil::getChinaTime())->get();
            foreach ($userNoticeTask as $item) {
                switch ($item->cl_Type) {
                    case 0:
                        if (isset($msgList[$item->cl_Type]) && isset($msgPriceList[$item->cl_Type])) {
                            $msg = $msgList[$item->cl_Type];
                            $msgPrice = $msgPriceList[$item->cl_Type];
                            $user = User::find($item->cl_UserId);
                            if (empty($user) || $user->cl_Status != 1 || $user->user_money < $msgPrice)
                                continue;

                            //发送语音通知并扣用户余额
                            $ihuyiResult = IhuyiService::voice($item->cl_Phone, $msg);
                            if ($ihuyiResult['SubmitResult']['code'] == 2) {
                                $item->update(['cl_Status' => 1]);
                                $user->decrement('user_money', $msgPrice);
                            } else {
                                $item->update(['cl_Remark' => $ihuyiResult['SubmitResult']['code'] . ':' . $ihuyiResult['SubmitResult']['msg']]);
                            }
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
