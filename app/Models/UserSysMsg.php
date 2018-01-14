<?php
/**
 * model
 * User: wdy
 * Date: 2016/2/2
 * Time: 9:53
 */

namespace App\Models;

use App\Util\TimeUtil;

class UserSysMsg extends Base
{
    protected $table = 'tab_user_sys_msg';

    /**
     * 用户订单支付消息添加
     * @param $userId
     * @param $unionId
     * @param $amount
     * @return mixed
     */
    public static function userOrderPayAdd($userId, $unionId, $amount, $payment = 3)
    {
        $userSysMsg['cl_UserId'] = $userId;
        $userSysMsg['cl_UnionId'] = $unionId;
        $userSysMsg['cl_Type'] = 1;
        $userSysMsg['cl_Status'] = 1;
        $userSysMsg['cl_Title'] = '会员订单支付通知';
        $userSysMsg['cl_Amount'] = $amount;
        $userSysMsg['cl_Payment'] = $payment;
        $userSysMsg['cl_CreateTime'] = TimeUtil::getChinaTime();
        return UserSysMsg::create($userSysMsg);
    }

    public static function ePayRefundAdd($userId, $unionId, $amount, $payment = 0)
    {
        $userSysMsg['cl_UserId'] = $userId;
        $userSysMsg['cl_UnionId'] = $unionId;
        $userSysMsg['cl_Type'] = 3;
        $userSysMsg['cl_Status'] = 1;
        $userSysMsg['cl_Title'] = '退款记录';
        $userSysMsg['cl_Amount'] = $amount;
        $userSysMsg['cl_Payment'] = $payment;
        $userSysMsg['cl_CreateTime'] = TimeUtil::getChinaTime();
        return UserSysMsg::create($userSysMsg);
    }
}