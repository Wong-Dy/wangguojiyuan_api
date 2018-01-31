<?php namespace App\Http\Controllers\API;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Jobs\APICmds\MapCmd;
use App\Models\Advertisement;
use App\Models\AppFeedback;
use App\Models\City;
use App\Models\Developer;
use App\Models\Notice;
use App\Models\ReportComplaints;
use App\Models\Scenic;
use App\Models\ScenicLayer;
use App\Models\ScenicPic;
use App\Models\ScenicSpot;
use App\Models\ScenicSpotComment;
use App\Models\ScenicSpotVoice;
use App\Models\User;
use App\Models\UserJourney;
use App\Util\EncryptUtil;
use App\Util\TimeUtil;
use App\Util\Tool;
use App\Util\ValidateUtil;
use App\XmlParse\CEnumError;
use App\XmlParse\CXMLCmdMgr;
use App\XmlParse\CXMLDataItem;
use App\XmlParse\CXMLReturn;
use Input, Notification, Session, Cache, Exception;
use Illuminate\Http\Request;

class RunController extends Controller
{
    protected $exceptionMsg = '服务器宝宝走神了';
    protected $logPath = '/logs/api/run/';

    public function __construct()
    {
    }

    public function getTest()
    {
        return User::count();
        $time1 = TimeUtil::parseTimestamp('2017-1-1 10:10');
        $time2 = TimeUtil::parseTimestamp('2017-1-1 10:12');

        echo $time2 - $time1;
    }

    public function anyMap()
    {
        $isEncryp = 0;
        set_time_limit(600);//设置超时时间
        try {
            //$_POST['fieldname'];	说明：只能接收Content-Type: application/x-www-form-urlencoded提交的数据
            //file_get_contents(“php://input”); 不能用于 enctype=”multipart/form-data
            //对于未指定 Content-Type 的POST数据，则可以使用file_get_contents(“php://input”);来获取原始数据
            if (!Input::has('strXml'))
                $strXml = file_get_contents("php://input");
            else
                $strXml = Input::get('strXml');

            if (!$strXml)
                return CXMLReturn::error(CEnumError::$OtherError, "内容指令为空");

            $strXml = rawurldecode($strXml);
            Tool::writeLog($strXml, 'RunCmd-strXml', $this->logPath);

            if ($isEncryp) {
                if (!Input::has('account') || trim(Input::get('account')) == '')
                    return CXMLReturn::error(CEnumError::$OtherError, "开发者帐号为空！");

                $ApiAuth = Developer::where('cl_Name', trim(Input::get('account')))->first();
                if (!$ApiAuth || $ApiAuth->cl_Status == 1)
                    return CXMLReturn::error(CEnumError::$OtherError, "开发者不存在或已禁用！");

                $EncryMode = new EncryptUtil();
                $ApiAuthType = $ApiAuth->cl_Type;  //解码加密方式
                $strXml = $EncryMode->$ApiAuthType($strXml, $ApiAuth->cl_Pwd, 'decode');    //解密
                Tool::writeLog($strXml, 'RunCmd-strXml-解密', $this->logPath);
            }

            try {
                $xmlMgr = new CXMLCmdMgr();
                $xmlMgr->LoadXML($strXml);  //解析xml
            } catch (Exception $e) {
                return CXMLReturn::error(CEnumError::$OtherError, "解密或解析失败");
            }

            $cmd = new MapCmd();
            switch ($xmlMgr->m_strName) {
                case 'Geocoding':
                    $strRetXml = $cmd->geocoding($xmlMgr);                //地理编码 或 反向地理编码
                    break;
                default:
                    return CXMLReturn::error(CEnumError::$OtherError, "无效指令");
                    break;
            }

        } catch (Exception $e) {
            return CXMLReturn::error(CEnumError::$Exception, $this->exceptionMsg . $e->getMessage());
        }
        return $strRetXml;
    }


}

