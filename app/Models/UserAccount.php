<?php
/**
 * 用户资金流动表
 * User: wdy
 * Date: 2016/2/2
 * Time: 9:53
 */

namespace App\Models;

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
}