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

    public static function recharge($userId, $amount, $note = '', $payment = 0, $system = 0)
    {
        $payment['user_id'] = $userId;
        $payment['amount'] = $amount;
        $payment['cl_System'] = $system;
        $payment['add_time'] = time();
        $payment['paid_time'] = time();
        $payment['process_type'] = 0;
        $payment['is_paid'] = 0;
        $payment['cl_Payment'] = $payment;
        $payment['user_note'] = $note;
        $payment['cl_TradeNo'] = SeqNo::getOrderCode(CGlobal::SEQNO_USER_ACCOUNT_TRADE_NO, 're');
        return UserAccount::create($payment);
    }
}