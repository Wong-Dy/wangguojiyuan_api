<?php
/**
 * BBSXML解析List集合类
 * @author  wdy
 * @date    2016-01-11
 */

namespace App\XmlParse;

class CBBSXMLDataItem extends CXMLBase{

    public $m_ListImage = array();
    public $m_ListPraise = array();
    public $m_ListReplye = array();

    public function GetXML(){
        $strXML = "";
        $strXML .= "<Item>";

        if (count($this->m_asParamName) > 0) {
            for ($nIndex = 0; $nIndex < count($this->m_asParamName); $nIndex++) {
                $strXML .= "<" . trim($this->m_asParamName[$nIndex]) . ">";
                $strXML .= "<![CDATA[" .  trim($this->m_asParamData[$nIndex]) . "]]>";
                $strXML .= "</" . trim($this->m_asParamName[$nIndex]) . ">";
            }
        }

        if (count($this->m_ListImage) > 0) {
            $strItemName = "ImageUrls";
            $strXML .= "<". $strItemName .">";
            foreach ($this->m_ListImage as $item)
            {
                $strXML .= $item->GetXML();
            }

            $strXML .= "</". $strItemName .">";
        }

        if (count($this->m_ListPraise) > 0) {
            $strItemName = "PraiseUsers";
            $strXML .= "<". $strItemName .">";
            foreach ($this->m_ListPraise as $item)
            {
                $strXML .= $item->GetXML();
            }

            $strXML .= "</". $strItemName .">";
        }

        if (count($this->m_ListReplye) > 0) {
            $strItemName = "ReplyUsers";
            $strXML .= "<". $strItemName .">";
            foreach ($this->m_ListReplye as $item)
            {
                $strXML .= $item->GetXML();
            }

            $strXML .= "</". $strItemName .">";
        }

        $strXML .= "</Item>";
        return $strXML;
    }



}