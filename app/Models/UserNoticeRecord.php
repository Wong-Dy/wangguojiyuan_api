<?php
/**
 * 用户通知记录 模型
 * User: wdy
 * Date: 2018/01/10
 * Time: 22:49
 */

namespace App\Models;

use App\Util\TimeUtil;

class UserNoticeRecord extends Base
{
    protected $table = 'tab_user_notice_record';

    public function getTypeDesc()
    {
        $arr = getSelectList('voice_notice_type');
        return isset($arr[$this->cl_Type]) ? $arr[$this->cl_Type] : '其他';
    }

    public static function add($userId, $title, $phone, $type = 0, $sendUserId = 0, $remark = '')
    {
        $ret = self::create([
            'cl_UserId' => $userId,
            'cl_Title' => $title,
            'cl_Phone' => $phone,
            'cl_Type' => $type,
            'cl_SendUserId' => $sendUserId,
            'cl_Remark' => $remark,
            'cl_NoticeTime' => TimeUtil::getChinaTime(),
            'cl_CreateTime' => TimeUtil::getChinaTime(),
        ]);
        return $ret;
    }
}