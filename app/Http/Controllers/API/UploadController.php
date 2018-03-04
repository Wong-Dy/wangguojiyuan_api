<?php
/**
 * 上传API
 * User: wdy
 * Date: 2016/1/21
 * Time: 17:58
 */

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\JsonParse\JErrorCode;
use App\JsonParse\JReturn;
use App\Models;
use App\Util\Comm;
use App\Util\TimeUtil;
use App\Util\Tool;
use App\XmlParse\CEnumError;
use App\XmlParse\CXMLReturn;
use Request;

class UploadController extends Controller {
    protected $exError = "接口出问题了";
    protected $logPath = "/logs/api/upload/";
    protected $result;
    protected $size = 0;  //限制上传字节 byte

    public function __construct(Request $request)
    {
        $this->field = 'file';
        $this->request = $request;
    }

    public function postHead()
    {
        $this->size = 1548576;
        $field = $this->field;
        $path = "/uploads/user/headimg/";
        $absolutePath = public_path() . $path;
        $request = $this->request;

        try {
            if ($request::hasFile($field)) {
                $pic = $request::file($field);
                if ($pic->isValid()) {
                    if ($pic->getClientSize() > $this->size)
                        return CXMLReturn::error(1, "file size does not match");

                    $newName = md5(rand(1, 99999) . $pic->getClientOriginalName()) . "." . $pic->getClientOriginalExtension();
                    $pic->move($absolutePath, $newName);

                    $url = $path . $newName;
                    $param['path'] = $url;
                    $param['url'] = CUSTOM_SITE_HOST . $url;
                    $param['size'] = sprintf('%.2f', $pic->getClientSize() / 1024 / 1024);

                    return CXMLReturn::result(CEnumError::$Success, "", $param);
                }
            }
        } catch (\Exception $e) {
            Tool::writeLog($e->getMessage(), 'postHead', $this->logPath);
            $this->result = CXMLReturn::error(CEnumError::$Exception, $this->exError);
        }
        return $this->result;
    }

    public function postWxGamebbsImg()
    {
        $this->size = 10485760;
        $field = $this->field;
        $path = "/uploads/wx/goods/" . TimeUtil::getChinaTime('Ymd') . '/';
        $absolutePath = public_path() . $path;

        $request = $this->request;

        try {
            if ($request::hasFile($field)) {
                $pic = $request::file($field);

                if ($pic->isValid()) {
                    if ($pic->getClientSize() < 1 || $pic->getClientSize() > $this->size)
                        return JReturn::error(JErrorCode::ERROR, '文件大小不匹配');

                    $guid = Comm::getGuid();
                    $newName = $guid . "." . $pic->getClientOriginalExtension();
                    $pic->move($absolutePath, $newName);

                    $param['Path'] = $path . $newName;
                    $param['Url'] = CUSTOM_API_HOST . $path . $newName;
                    $param['Size'] = sprintf('%.2f', $pic->getClientSize() / 1024 / 1024);
                    $this->result = JReturn::result(JErrorCode::SUCCESS, $param);

                } else {
                    $this->result = JReturn::error(JErrorCode::ERROR, '上传失败');
                }
            }
        } catch (\Exception $e) {
            Tool::writeLog($e, __FUNCTION__, $this->logPath);
            $this->result = JReturn::error(JErrorCode::EXCEPTION_ERROR, $this->exError);
        }

        Tool::writeLog($this->result);
        return $this->result;
    }
}