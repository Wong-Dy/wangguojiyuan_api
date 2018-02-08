<?php
/**
 * 用户资金流动表
 * User: wdy
 * Date: 2016/2/2
 * Time: 9:53
 */

namespace App\Models;

use App\Util\CGlobal;
use App\Util\SeqNo;

class UserAccount extends Base
{
    protected $table = 'wdy_user_account';
    protected $primaryKey = "id";

    public function getPayDesc()
    {
        if (empty($this->cl_Payment))
            return '';
        return getSelectList('payment')[$this->cl_Payment];
    }

    public static function recharge($userId, $amount, $note = '', $is_paid = 0, $payment = 0, $system = 0)
    {
        $modelData['user_id'] = $userId;
        $modelData['amount'] = $amount;
        $modelData['cl_System'] = $system;
        $modelData['add_time'] = time();
        $modelData['paid_time'] = time();
        $modelData['process_type'] = 0;
        $modelData['is_paid'] = $is_paid;
        $modelData['cl_Payment'] = $payment;
        $modelData['user_note'] = $note;
        $modelData['cl_TradeNo'] = SeqNo::getOrderCode(CGlobal::SEQNO_USER_ACCOUNT_TRADE_NO, 're');
        return UserAccount::create($modelData);
    }
}