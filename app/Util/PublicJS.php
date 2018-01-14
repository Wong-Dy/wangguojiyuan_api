<?php
/**
 * 共用js提示类
 * User: wdy
 * Date: 2016/2/2
 * Time: 9:44
 */

namespace App\Util;


class PublicJS
{
//==========================================
    //前台弹出框
    //==========================================
    public static function alert($description)
    {
        $strRet = "";
        $strRet .= "<script language=JavaScript>\n";
        $strRet .= "alert('" . $description . "');\n";
        $strRet .= "</script>\n";
        return $strRet;
    }

    //==========================================
    //转向页面
    //==========================================
    public static function redirect($url)
    {
        $strRet = "";
        $strRet .= "<script language=JavaScript>\n";
        $strRet .= "window.location='" . $url . "';\n";
        $strRet .= "</script>\n";
        return $strRet;
    }

    //==========================================
    //弹出对话框，转向所指页面
    //==========================================
    public static function msgBoxRedirect($description, $url = "")
    {
        $strRet = "";
        $strRet .= "<script language=JavaScript>\n";
        $strRet .= "alert('" . $description . "');\n";
        if($url != "")
            $strRet .= "window.location='" . $url . "';\n";
        else
            $strRet .= "history.go(-1);\n";
        $strRet .= "</script>\n";
        return $strRet;
    }

    public static function phoneFinish($toast)
    {
        $strRet = "";
        $strRet .= "<script language=JavaScript>\n";
        $strRet .= "window.JavaScriptInterface.FinishAndToast('".$toast."');\n";
        $strRet .= "</script>\n";
        return $strRet;
    }
}