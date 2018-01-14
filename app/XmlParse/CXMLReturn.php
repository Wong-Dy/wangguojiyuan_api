<?php
/**
 * 生成返回XML类
 * @author  wdy
 * @date    2016-01-11
 */

namespace App\XmlParse;

class CXMLReturn
{
    public static function success()
    {
        $retMgr = new CXMLCmdMgr(true);
        return $retMgr->GetXML();
    }

    public static function result($code, $msg, $param = null, $list = null)
    {
        $retMgr = new CXMLCmdMgr();
        $retMgr->SetError($code, $msg);

        if (!empty($param)) {
            foreach ($param as $key => $value) {
                $retMgr->AddProp($key, $value);
            }
        }

        if (!empty($list)) {
            $retMgr->SetPropList($list);
        }
        $strRet = $retMgr->GetXML();
        return $strRet;
    }

    public static function error($code, $msg)
    {
        $retMgr = new CXMLCmdMgr();
        $retMgr->SetError($code, $msg);
        return $retMgr->GetXML();
    }

}