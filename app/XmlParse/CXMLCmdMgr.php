<?php
/**
 * XML解析类
 * @author  wdy
 * @date    2016-01-11
 */

namespace App\XmlParse;

class CXMLCmdMgr extends CXMLBase
{
    public function __construct($is_init = false)
    {
        if ($is_init)
            $this->SetError(0, "");
    }

    public static function GetParseXmlForJson($strJson)
    {
        $arr = json_decode($strJson);
        $xmlMgr = new self();
        $xmlMgr->AddCmdName($arr->cmd);

        $strList = "";
        if (!empty($arr->param)) {
            foreach ($arr->param as $k => $v) {
                if ($k == 'list') {
                    for ($i = 0; $i < count($v); $i++) {
                        $strList .= "<Item>";
                        foreach ($v[$i] as $k1 => $v1) {
                            $strList .= "<$k1>$v1</$k1>";
                        }
                        $strList .= "</Item>";
                    }
                    $xmlMgr->AddParam("DataList", $strList);
                    continue;
                }
                $xmlMgr->AddParam($k, $v);
            }
        }
        if (!empty($arr->prop)) {
            foreach ($arr->prop as $k => $v) {
                $xmlMgr->AddParam($k, $v);
            }
        }

        $strXml = $xmlMgr->GetXML();
        return $strXml;
    }

    public static function GetParseJsonForRetXml($strXml, $listName = "DataList")
    {
        $cmdmrg = new self();
        $cmdmrg->LoadXML($strXml);
        $Param = [];
        for ($i = 0; $i < count($cmdmrg->m_asPropName); $i++) {
            $name = $cmdmrg->m_asPropName[$i];
            $value = $cmdmrg->m_asPropData[$i];
            if (null != $listName && $listName == $name) {
                $listCXMLDataItem = $cmdmrg->AnalyticXML_ItemList($value, 'Prop');
                $list = [];
                foreach ($listCXMLDataItem as $item) {
                    $iParam = [];
                    for ($j = 0; $j < count($item->m_asPropName); $j++) {
                        $iname = $item->m_asPropName[$j];
                        $ivalue = $item->m_asPropData[$j];
                        $iParam[$iname] = $ivalue;
                    }
                    $list[] = $iParam;
                }
                $Param[$listName] = $list;
                continue;
            }
            $Param[$name] = $value;
        }
        return json_encode($Param);
    }

    public function ParseJson($listName = "DataList")
    {
        $cmdmrg = $this;
        $Param = [];
        for ($i = 0; $i < count($cmdmrg->m_asParamName); $i++) {
            $name = $cmdmrg->m_asParamName[$i];
            $value = $cmdmrg->m_asParamData[$i];
            if (null != $listName && $listName == $name) {
                $listCXMLDataItem = $cmdmrg->AnalyticXML_ItemList($value);
                $list = [];
                foreach ($listCXMLDataItem as $item) {
                    $iParam = [];
                    for ($j = 0; $j < count($item->m_asParamName); $j++) {
                        $iname = $item->m_asParamName[$j];
                        $ivalue = $item->m_asParamData[$j];
                        $iParam[$iname] = $ivalue;
                    }
                    $list[] = $iParam;
                }
                $Param[$listName] = $list;
                continue;
            }
            $Param[$name] = $value;
        }
        return json_decode(json_encode($Param));
    }

    public function GetXML()
    {
        $strXML = "";
        $strXML .= "<Body>";

        if (!empty($this->m_strName))
            $strXML .= "<Name>" . $this->m_strName . "</Name>";

        if (count($this->m_asParamName) > 0) {
            $strXML .= "<Param>";
            for ($nIndex = 0; $nIndex < count($this->m_asParamName); $nIndex++) {
                $strXML .= "<" . trim($this->m_asParamName[$nIndex]) . ">";
                $strXML .= trim($this->m_asParamData[$nIndex]);
                $strXML .= "</" . trim($this->m_asParamName[$nIndex]) . ">";
            }
            $strXML .= "</Param>";
        } else {
            $strXML .= "<Prop>";

            if (count($this->m_asPropName) > 0) {

                for ($nIndex = 0; $nIndex < count($this->m_asPropName); $nIndex++) {
                    $strXML .= "<" . trim($this->m_asPropName[$nIndex]) . ">";
                    $strXML .= trim($this->m_asPropData[$nIndex]);
                    $strXML .= "</" . trim($this->m_asPropName[$nIndex]) . ">";
                }

            }

            if (count($this->m_PropListItem) > 0) {
                $strXML .= "<DataList>";
                foreach ($this->m_PropListItem as $item) {
                    $strXML .= $item->GetXML();
                }

                $strXML .= "</DataList>";
            }

            $strXML .= "</Prop>";

        }

        $strXML .= "</Body>";
        return $strXML;
    }

    /**
     * 设置错误信息
     * @param $code
     * @param $msg
     */
    public function SetError($code, $msg)
    {
        if (!in_array("ErrorCode", $this->m_asPropName))
            $this->AddProp("ErrorCode", $code);
        else
            $this->SetPropData("ErrorCode", $code);

        if (!in_array("ErrorInfo", $this->m_asPropName))
            $this->AddProp("ErrorInfo", $msg);
        else
            $this->SetPropData("ErrorInfo", $msg);
    }

    /**
     * XML解析Cmd
     * @param $strXML
     */
    public function LoadXML($strXML)
    {
        $xml = simplexml_load_string($strXML);
        if ($xml) {
            foreach ($xml as $item) {
                if ($item->getName() == "Name") {
                    $this->m_strName = (String)$xml->Name;
                } else if ($item->getName() == "Param") {
                    $this->LoadXML_Param($item);
                } else if ($item->getName() == "Prop") {
                    $this->LoadXML_Prop($item);
                }
            }
        }
    }

    //==========================================
    //解析Param的xml
    //==========================================
    public function LoadXML_Param($XMLElement)
    {
        $xmlNodeList = $XMLElement->children();
        foreach ($xmlNodeList as $item) {
            $strValue = $item->asXML();

            $this->m_asParamName[] = $item->getName();
            if (count($item->children()) > 0) {                              //存在子节点
                $this->m_asParamData[] = $strValue;
                $this->m_ParamDataList = $this->AnalyticXML_ItemList($strValue);
            } else {
                $this->m_asParamData[] = $this->GetInnerText($strValue);    //只获取文本  123222
            }

        }
    }

    //==========================================
    //解析Prop的xml
    //==========================================
    public function LoadXML_Prop($XMLElement)
    {
        $xmlNodeList = $XMLElement->children();
        foreach ($xmlNodeList as $item) {
            $strValue = $item->asXML();

            $this->m_asPropName[] = $item->getName();

            if (count($item->children()) > 0) {  //存在子节点
                $this->m_asPropData[] = $strValue;
                $this->m_PropDataList = $this->AnalyticXML_ItemList($strValue);
            } else
                $this->m_asPropData[] = $this->GetInnerText($strValue);//只获取文本

        }
    }

    public function AnalyticXML_ItemList($strXML, $strItemType = "Param")
    {
        $list = array();
        $xml = simplexml_load_string($strXML);
        foreach ($xml as $item) {
            if ($item->getName() == "Item") {
                $data = new CXMLDataItem();
                foreach ($item->children() as $xmlNodeItems) {
                    $strValue = $xmlNodeItems->asXML();
                    if ($strItemType == "Param") {
                        $data->m_asParamName[] = $xmlNodeItems->getName();
                        $data->m_asParamData[] = (count($xmlNodeItems->children()) > 0 ?$strValue : $this->GetInnerText($strValue));
                    } else if ($strItemType == "Prop") {
                        $data->m_asPropName[] = $xmlNodeItems->getName();
                        $data->m_asPropData[] = (count($xmlNodeItems->children()) > 0 ? $strValue : $this->GetInnerText($strValue));
                    }
                }
                $list[] = $data;
            }
        }
        return $list;
    }


    public function AnalyticXML_ItemListByName($strXML, $Name, $strItemType = "Param")
    {
        $list = array();
        $xml = simplexml_load_string($strXML);
        foreach ($xml as $item) {
            if ($item->getName() == $Name) {
                $data = new CXMLDataItem();
                foreach ($item->children() as $xmlNodeItems) {
                    $strValue = $xmlNodeItems->asXML();
                    if ($strItemType == "Param") {
                        $data->m_asParamName[] = $xmlNodeItems->getName();
                        $data->m_asParamData[] = $this->GetInnerText($strValue);//只获取文本
                    } else if ($strItemType == "Prop") {
                        $data->m_asPropName[] = $xmlNodeItems->getName();
                        $data->m_asPropData[] = $this->GetInnerText($strValue);//只获取文本
                    }
                }
                $list[] = $data;
            }
        }
        return $list;
    }

}
