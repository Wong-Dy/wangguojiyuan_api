<?php
/**
 * User: Administrator
 * Date: 2016/12/15
 * Time: 15:34
 */

namespace App\Jobs\APICmds\Applet\WeiXin;

use App\JsonParse\JErrorCode;
use App\Models\AdPosition;
use App\Models\Advertisement;

class ALWAdvertisementCmd extends BaseCmd
{
    public function __construct($jsonData)
    {
        parent::__construct($jsonData);
        $this->logPath .= 'advertisement/';
    }

    public function getAdvertisement()
    {
        $data = $this->jsonData;
        try {
            if (!isset($data->code) || !isset($data->system) || !isset($data->pageIndex) || !isset($data->pageSize))
                return $this->error(JErrorCode::LACK_PARAM_ERROR);

            $adPosition = AdPosition::valid()->where('cl_System', $data->system)->where('cl_Code', $data->code)->first();
            if (empty($adPosition))
                return $this->error(JErrorCode::CUSTOM_SELECT_NOT_FOUND);

            $dataList = Advertisement::valid()->where('cl_PId', $adPosition->cl_Id)->orderby('cl_Order', 'desc')->page($data->pageIndex)->paginate($data->pageSize);
            foreach ($dataList as $item) {
                $result_item = $this->std();

                $result_item->title = $item->cl_Title;
                $result_item->type = $item->cl_Type;
                $result_item->content = $item->cl_Content;
                $result_item->link = $item->cl_Link;
                $result_item->createTime = $item->cl_CreateTime;
                $result_item->fileUrl = CUSTOM_HTTPS_SITE_HOST . $item->cl_FilePath;

                $this->result_list[] = $result_item;
            }

            return $this->result();
        } catch (\Exception $e) {
            return $this->exception($e);
        }
    }

}