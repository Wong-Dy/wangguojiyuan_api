<?php
/**
 * User: Administrator
 * Date: 2016/12/15
 * Time: 15:34
 */

namespace App\Jobs\APICmds\Applet\WeiXin;

use App\JsonParse\JErrorCode;
use App\Models\AppFeedback;
use App\Models\WXUser;
use App\Util\TimeUtil;

class ALWSystemCmd extends BaseCmd
{
    public function __construct($jsonData)
    {
        parent::__construct($jsonData);
        $this->logPath .= 'system/';
    }

    public function addFeedBack()
    {
        $data = $this->jsonData;
        try {
            if (!isset($data->appName) ||  !isset($data->score) || !isset($data->content))
                return $this->error(JErrorCode::LACK_PARAM_ERROR);

            $wxUser = WXUser::where('cl_OpenId', $data->m_openId)->first();
            if (empty($wxUser))
                return $this->error(JErrorCode::WX_USER_INFO_NOT_FOUND_ERROR);

            $user = $wxUser->user;

            $model['cl_Account'] = $user->user_name;
            $model['cl_AppName'] = $data->appName;
            $model['cl_Phone'] = $user->mobile_phone;
            $model['cl_Score'] = $data->score;
            $model['cl_Content'] = $data->content;
            $model["cl_CreateTime"] = TimeUtil::getChinaTime();

            if (!$ret_gid = AppFeedback::insertGetId($model))
                return $this->errori('添加反馈失败');

            return $this->success();
        } catch (\Exception $e) {
            return $this->exception($e);
        }
    }


}