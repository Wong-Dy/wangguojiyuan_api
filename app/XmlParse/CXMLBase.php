<?php
/**
 * XML解析父类
 * @author  wdy
 * @date    2016-01-11
 */

namespace App\XmlParse;

use DOMDocument;

class CXMLBase
{
    /// 指令名称
    public $m_strName = "";
    /// 节点的名称
    public $m_asParamName = array();
    /// 节点的值
    public $m_asParamData = array();

    public $m_asPropName = array();
    public $m_asPropData = array();
    public $m_asPropIsXml = array();

    // CXMLDataItem Param数据列表
    public $m_ParamListItem = array();
    // CXMLDataItem Prop数据列表
    public $m_PropListItem = array();

    // param数据集合
    public $m_ParamDataList = array();
    // prop数据集合
    public $m_PropDataList = array();

    public function AddCmdName($strName)
    {
        $this->m_strName = $strName;
    }

    public function AddParam($strName, $strData)
    {
        array_push($this->m_asParamName, $strName);
        array_push($this->m_asParamData, $strData);
    }

    public function AddProp($strName, $strData, $isXml = 0)
    {
        if (strstr($strData, '<Item>') !== false && strstr($strName, 'List') != false)
            $isXml = 1;
        array_push($this->m_asPropName, $strName);
        array_push($this->m_asPropData, $strData);
        array_push($this->m_asPropIsXml, $isXml);
    }

    public function SetParamList($list)
    {
        $this->m_ParamListItem = $list;
    }

    public function SetPropList($list)
    {
        $this->m_PropListItem = $list;
    }

    public function GetParamData($strName)
    {
        $strValue = "";
        for ($i = 0; $i < count($this->m_asParamName); $i++) {
            if (trim($this->m_asParamName[$i]) == $strName) {
                $strValue = trim($this->m_asParamData[$i]);
                break;
            } else
                $strValue = "";
        }
        return $strValue;
    }

    public function SetParamData($strName, $strValue)
    {
        for ($i = 0; $i < count($this->m_asParamName); $i++) {
            if (trim($this->m_asParamName[$i]) == $strName) {
                $this->m_asParamData[$i] = $strValue;
                break;
            }
        }
    }

    public function GetParamDataToInt($strName)
    {
        $nValue = 0;

        for ($nIndex = 0; $nIndex < count($this->m_asParamName); $nIndex++) {
            if (trim($this->m_asParamName[$nIndex]) == $strName) {
                $nValue = (int)trim($this->m_asParamData[$nIndex]);
                break;
            } else
                $nValue = 0;
        }
        return $nValue;
    }

    public function GetPropData($strName)
    {
        $strValue = "";
        for ($i = 0; $i < count($this->m_asPropName); $i++) {
            if (trim($this->m_asPropName[$i]) == $strName) {
                $strValue = trim($this->m_asPropData[$i]);
                break;
            } else
                $strValue = "";
        }
        return $strValue;
    }

    public function SetPropData($strName, $strValue)
    {
        for ($i = 0; $i < count($this->m_asPropName); $i++) {
            if (trim($this->m_asPropName[$i]) == $strName) {
                $this->m_asPropData[$i] = $strValue;
                break;
            }
        }
    }

    public function GetPropDataToInt($strName)
    {
        $nValue = 0;

        for ($nIndex = 0; $nIndex < count($this->m_asPropName); $nIndex++) {
            if (trim($this->m_asPropName[$nIndex]) == $strName) {
                $nValue = (int)trim($this->m_asPropData[$nIndex]);
                break;
            } else
                $nValue = 0;
        }
        return $nValue;
    }

    public function GetInnerText($strXml)
    {
        $dom = new DOMDocument();
        $dom->loadXML((String)$strXml);
        return $dom->textContent;
    }
}
