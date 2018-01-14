<?php
/**
 * XML解析List集合类
 * @author  wdy
 * @date    2016-01-11
 */

namespace App\XmlParse;

class CXMLDataItem extends CXMLBase
{

    public function GetXML($type = 'Prop')
    {
        $strXML = "";
        $strXML .= "<Item>";

        if ($type == 'Param') {
            if (count($this->m_asParamName) > 0) {
                for ($nIndex = 0; $nIndex < count($this->m_asParamName); $nIndex++) {
                    $strXML .= "<" . trim($this->m_asParamName[$nIndex]) . ">";
                    $strXML .= "<![CDATA[" . trim($this->m_asParamData[$nIndex]) . "]]>";
                    $strXML .= "</" . trim($this->m_asParamName[$nIndex]) . ">";
                }
            }
        } else if ($type == 'Prop') {
            if (count($this->m_asPropName) > 0) {
                for ($nIndex = 0; $nIndex < count($this->m_asPropName); $nIndex++) {
                    $strXML .= "<" . trim($this->m_asPropName[$nIndex]) . ">";
                    if (!$this->m_asPropIsXml[$nIndex])
                        $strXML .= "<![CDATA[" . trim($this->m_asPropData[$nIndex]) . "]]>";
                    else
                        $strXML .= trim($this->m_asPropData[$nIndex]);
                    $strXML .= "</" . trim($this->m_asPropName[$nIndex]) . ">";
                }
            }
        }


        $strXML .= "</Item>";
        return $strXML;
    }

    public function ParseJson()
    {
        $cmdmrg = $this;
        $Param = [];
        for ($i = 0; $i < count($cmdmrg->m_asParamName); $i++) {
            $name = $cmdmrg->m_asParamName[$i];
            $value = $cmdmrg->m_asParamData[$i];

            $Param[$name] = $value;
        }
        return json_decode(json_encode($Param));
    }

}