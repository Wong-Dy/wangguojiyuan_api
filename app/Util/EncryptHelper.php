<?php
/**
 * 通用加密帮助库类
 *
 * 加密方式如下：
 * 第一步：对参数按照key=value的格式，并按照参数名ASCII字典序排序如下：
 * stringA="Account=115891324654&Integral=1000&Source=1"
 * 第二步：拼接API密钥：
 * stringSignTemp=stringA+"&key=192006250b4c09247ec02edce69f6a2d"sign=MD5(stringSignTemp).toUpperCase()="9A0A8659F005D6984697E2CA0A9CF3B7"
 *
 * @author  wdy
 * @date    2016-01-11
 */

namespace App\Util;

class EncryptHelper
{
    function __construct()
    {
    }

    function trimString($value)
    {
        $ret = null;
        if (null != $value) {
            $ret = $value;
            if (strlen($ret) == 0) {
                $ret = null;
            }
        }
        return $ret;
    }

    /**
     *    作用：产生随机字符串，不长于32位
     */
    public function createNoncestr($length = 32)
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    /**
     *    作用：格式化参数，签名过程需要使用
     */
    function formatBizQueryParaMap($paraMap, $urlencode)
    {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v) {
            if ($urlencode) {
                $v = urlencode($v);
            }
            //$buff .= strtolower($k) . "=" . $v . "&";
            $buff .= $k . "=" . $v . "&";
        }
        $reqPar = "";
        if (strlen($buff) > 0) {
            $reqPar = substr($buff, 0, strlen($buff) - 1);
        }
        return $reqPar;
    }

    /**
     *    作用：生成签名
     */
    public function getSign($Obj)
    {
        foreach ($Obj as $k => $v) {
            $Parameters[$k] = $v;
        }
        //签名步骤一：按字典序排序参数
        ksort($Parameters);
        $String = $this->formatBizQueryParaMap($Parameters, false);
        //echo '【string1】'.$String.'</br>';
        //签名步骤二：在string后加入KEY
        $String = $String . "&key=" . Config::get('cache.MD5EncryKey');
        //echo "【string2】".$String."</br>";
        //签名步骤三：MD5加密
        $String = md5($String);
        //echo "【string3】 ".$String."</br>";
        //签名步骤四：所有字符转为大写
        $result_ = strtoupper($String);
        //echo "【result】 ".$result_."</br>";
        return $result_;
    }

    /**
     *    作用：将xml转为array
     */
    public function xmlToArray($xmlMgr)
    {

        $arr = array();
        for ($i = 0; $i < count($xmlMgr->m_asParamName); $i++) {
            if (empty(trim($xmlMgr->m_asParamName[$i])))
                continue;
            if (strtoupper(trim($xmlMgr->m_asParamName[$i])) == strtoupper('Sign'))
                continue;
            $arr[trim($xmlMgr->m_asParamName[$i])] = trim($xmlMgr->m_asParamData[$i]);
        }
        return $arr;
    }

    /**
     *    作用：打印数组
     */
    function printErr($wording = '', $err = '')
    {
        print_r('<pre>');
        echo $wording . "</br>";
        var_dump($err);
        print_r('</pre>');
    }
}


