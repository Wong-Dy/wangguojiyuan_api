<?php
/**
 * User: Administrator
 * Date: 2016/12/15
 * Time: 15:34
 */

namespace App\Jobs\APICmds\Applet\WeiXin;


use App\JsonParse\JErrorCode;
use App\Models\GameBbs;
use App\Models\WXUser;
use App\Util\TimeUtil;

class ALWGameBbsCmd extends BaseCmd
{
    public function __construct($jsonData)
    {
        parent::__construct($jsonData);
        $this->logPath .= 'gamebbs/';
    }

    public function addGameBbs()
    {
        $data = $this->jsonData;
        try {
            if (!isset($data->content) || !isset($data->photoList))
                return $this->error(JErrorCode::LACK_PARAM_ERROR);

            $wxUser = WXUser::where('cl_OpenId', $data->m_openId)->first();
            if (empty($wxUser))
                return $this->error(JErrorCode::WX_USER_INFO_NOT_FOUND_ERROR);

            $user = $wxUser->user;

            $modelData = [
                'cl_UserId' => $user->user_id,
                'cl_CreateTime' => TimeUtil::getChinaTime(),
                'cl_Content' => $data->content,
                'cl_Photos' => implode(',', $data->photoList),
            ];

            GameBbs::create($modelData);

            return $this->success();
        } catch (\Exception $e) {
            return $this->exception($e);
        }
    }

    public function getGameBbs()
    {
        $data = $this->jsonData;
        try {
            if (!isset($data->pageIndex) || !isset($data->pageSize) || !isset($data->orderType))
                return $this->error(JErrorCode::LACK_PARAM_ERROR);

            $wxUser = WXUser::where('cl_OpenId', $data->m_openId)->first();
            if (empty($wxUser))
                return $this->error(JErrorCode::WX_USER_INFO_NOT_FOUND_ERROR);

            $user = $wxUser->user;

            $dataList = GameBbs::valid()->orderType($data->orderType)->page($data->pageIndex)->paginate($data->pageSize);
            foreach ($dataList as $item) {
                $result_item = $this->std();
                $result_item->content = $item->cl_Content;
                $result_item->userName = $item->user->alias;
                $result_item->userHead = $item->user->getHeadImg();
                $result_item->time = $item->cl_CreateTime;
                $result_item->like = $item->cl_Like;
                $result_item->isHot = $item->cl_IsHot;
                $result_item->photos = $item->getPhotoArr();

                $this->result_list[] = $result_item;
            }

            return $this->result();
        } catch (\Exception $e) {
            return $this->exception($e);
        }
    }

}