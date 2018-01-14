<?php
/**
 * 地图指令
 * User: Administrator
 * Date: 2016/12/15
 * Time: 15:34
 */

namespace App\Jobs\APICmds;

use App\Util\HttpUtil;
use App\XmlParse\CEnumError;
use App\XmlParse\CXMLReturn;

class MapCmd extends BaseCmd
{
    public function __construct()
    {
        $this->logPath .= 'map/';
    }

    public function geocoding($xmlMgr)
    {
        $data = $this->std();
        try {
            $data->Language = $xmlMgr->GetParamData('Language');
            $data->Address = $xmlMgr->GetParamData('Address');
            $data->Latlng = $xmlMgr->GetParamData('Latlng');
            $data->Type = $xmlMgr->GetParamData('Type');

            $param['Country'] = '';
            $param['Province'] = '';
            $param['City'] = '';
            $param['District'] = '';
            $param['Street'] = '';
            $param['StreetNumber'] = '';
            $param['Latitude'] = '';
            $param['Longitude'] = '';


            return CXMLReturn::result(CEnumError::$Success, '', $param);
        } catch (\Exception $e) {
            return CXMLReturn::error(CEnumError::$Exception, $this->exceptionMsg . "：" . $e->getMessage());
        }
    }
}