<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/12/15
 * Time: 15:43
 */

namespace App\Jobs\APICmds\Applet\WeiXin;

use App\JsonParse\JErrorCode;
use App\JsonParse\JReturn;
use App\Util\Tool;
use Input, Notification, Session, Cache, Exception;

class BaseCmd
{
    protected $exceptionMsg = '服务器宝宝走神了';
    protected $logPath = '/logs/jobs/apicmds/applet/weixin/';

    protected $jsonData;

    protected $result_code;
    protected $result_msg;
    protected $result_param;
    protected $result_list;
    protected $result_error_list;


    public function __construct($jsonData)
    {
        $this->jsonData = $jsonData;

        $this->result_code = JErrorCode::SUCCESS;
        $this->result_msg = '';
    }

    /**
     * 组装返回报文
     * @return string
     */
    public function result()
    {
        return JReturn::result($this->result_code, $this->result_param, $this->result_msg, $this->result_list);
    }

    /**
     * 返回成功报文
     * @return string
     */
    public function success()
    {
        return JReturn::success();
    }

    /**
     * 创建stdClass
     * @return \stdClass
     */
    public function std()
    {
        return new \stdClass();
    }

    /**
     * 返回失败报文
     * @param $info
     * @param int $code
     * @return string
     */
    public function error($code = 0, $info = '')
    {
        return JReturn::error($code, $info);
    }

    public function errori($info = '', $code = 0)
    {
        return JReturn::error($code, $info);
    }

    public function exception(\Exception $e)
    {
        Tool::writeLog($e, 'exception', $this->logPath);
        return JReturn::error(JErrorCode::EXCEPTION_ERROR);
    }

    /**
     * 解析赛狐定义二维码
     * @param $data 格式示例(sid=105;mid=001)
     * @return array
     */
    public function parseDataStyle1($data)
    {
        try {
            $dataList = [];
            $arr1 = explode(';', $data);
            foreach ($arr1 as $item) {
                $arr2 = explode('=', $item);
                $dataList[$arr2[0]] = $arr2[1];
            }
            return $dataList;
        } catch (Exception $e) {

        }
        return [];
    }
}