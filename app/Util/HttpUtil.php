<?php
/**
 * HTTP发着数据类
 * User: wdy
 * Date: 2016/1/11
 * Time: 17:34
 */

//GB2312的Encode
// echo urlencode("中文-_. ")."\n"; //%D6%D0%CE%C4-_.+
// echo urldecode("%D6%D0%CE%C4-_. ")."\n"; //中文-_.
// echo rawurlencode("中文-_. ")."\n"; //%D6%D0%CE%C4-_.%20
// echo rawurldecode("%D6%D0%CE%C4-_. ")."\n"; //中文-_.

// 除了“-_.”之外的所有非字母数字字符都将被替换成百分号“%”后跟两位十六进制数。

// urlencode和rawurlencode的区别：urlencode将空格编码为加号“+”，rawurlencode将空格编码为加号“%20”。

// 如果要使用UTF-8的Encode，有两种方法：

// 一、将文件存为UTF-8文件，直接使用urlencode、rawurlencode即可。

// 二、使用mb_convert_encoding函数：

// $url = 'http://s.jb51.net/中文.rar';
// echo urlencode(mb_convert_encoding($url, 'utf-8', 'gb2312'))."\n";
// echo rawurlencode(mb_convert_encoding($url, 'utf-8', 'gb2312'))."\n";
//http%3A%2F%2Fs.jb51.net%2F%E4%B8%AD%E6%96%87.rar

namespace App\Util;

use Exception;
use SoapClient;
use SoapHeader;

class HttpUtil
{
    public static function getClientIp()
    {
        $request = app('request');
        $request->setTrustedProxies(array('10.32.0.1/16'));
        $ip = $request->getClientIp();
        return $ip;
    }

    public static function getClientIp1()
    {
        $cIP = getenv('REMOTE_ADDR');
        $cIP1 = getenv('HTTP_X_FORWARDED_FOR');
        $cIP2 = getenv('HTTP_CLIENT_IP');
        $cIP1 ? $cIP = $cIP1 : null;
        $cIP2 ? $cIP = $cIP2 : null;
        return $cIP;
    }

    /**
     * 根据IP获取地址坐标信息(百度API)
     * @return mixed
     */
    public static function getIPAddress()
    {
        $ip = self::getClientIp();
        $mapIPUrl = 'http://api.map.baidu.com/location/ip?ip=' . $ip . '&ak=rZLLErqwq2aBiC2GsfI87ziN58M1Hc9l';
//        $mapIPUrl = 'http://api.map.baidu.com/location/ip?ak=imUYtFzGpKPsL0GzhKqjBQZGntSFqgxN';   //快易行
        $ret = self::sendPost($mapIPUrl);
        $jsonIpData = json_decode(urldecode($ret));
        return $jsonIpData;
    }

    /**
     * HTTP协议状态码,调用函数时候只需要将$num赋予一个下表中的已知值就直接会返回状态了。
     * @param $num
     * @return mixed
     */
    public static function getHttpCode($num)
    {
        $http = config('custom.http');
        return $http[$num];
//        header($http[$num]);
    }

    /**
     * 发送post请求  array $post_data post键值对数据
     * @param $url
     * @param array $post_data
     * @return mixed|string
     */
    public static function sendPost($url, $post_data = array("key" => "value"), $time_out = 30)
    {
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_HTTPGET, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, $time_out);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.3) Gecko/2008092417 Firefox/3.0.3');
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
            curl_setopt($ch, CURLOPT_URL, $url);

            $result = curl_exec($ch);
            curl_close($ch);
            return $result;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * 发送请求（get方式）
     * @param $url
     * @return mixed|string
     */
    public static function sendGet($url)
    {
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_HTTPGET, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.3) Gecko/2008092417 Firefox/3.0.3');
            curl_setopt($ch, CURLOPT_URL, $url);

            $result = curl_exec($ch);
            curl_close($ch);
            return $result;
        } catch (Exception $e) {
            return $e->getMessage();
        }

    }

    /**
     * 发送请求  例：HttpUtil::fetch_page('google',$url,'key=value');
     * @param $site
     * @param $url
     * @param bool $params
     * @return mixed|string
     */
    public static function fetch_page($site, $url, $params = false)
    {
        try {
            $ch = curl_init();
            $cookieFile = $site . '_cookiejar.txt';
            curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
            curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_HTTPGET, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            if ($params)
                curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.3) Gecko/2008092417 Firefox/3.0.3');
            curl_setopt($ch, CURLOPT_URL, $url);

            $result = curl_exec($ch);
            curl_close($ch);
            return $result;
        } catch (Exception $e) {
            return $e->getMessage();
        }

    }

    /**
     * 下载文件（get方式）
     * @param $url  下载地址
     * @param string $type 后缀名
     * @param string $src 存放路径
     * @return bool|string
     */
    public static function downFile($url, $type = "file", $src = "downfile", $isAbsolute = false)
    {
        $url = str_replace("\\", "", $url);
        $url = str_replace(" ", "%20", $url);

        if (!$isAbsolute)
            $file = public_path() . "/$src/";
        else
            $file = $src . "/";

        $name = Comm::getGuid() . ".$type";
        $file = $file . $name;

        if (!$isAbsolute)
            $path = "/$src/" . $name;
        else
            $path = $src . "/" . $name;

        $dir = pathinfo($file, PATHINFO_DIRNAME);
        !is_dir($dir) && @mkdir($dir, 0755, true);

        $temp = self::sendGet($url);
        if (!empty($temp) && @file_put_contents($file, $temp)) {
            return $path;
        } else {
            return false;
        }
    }

    /**
     * 发送webservice请求 ，调用RunCmd函数
     *
     * @param $url
     * @param $username
     * @param $pwd
     * @param $strXml
     * @return mixed
     */
    public static function toWebRunCmd($url, $username, $pwd, $strXml)
    {
        header("content-type:text/html;charset=utf-8");
        $client = new SoapClient($url);

        //头验证
        $u = new SoapHeader('http://www.xxx.com/', 'MyHeader', array('m_strUser' => $username, 'm_strPwd' => $pwd), true);

        //添加soapheader
        $client->__setSoapHeaders($u);

        $suc = $client->RunCmd(array('strXml' => $strXml));
        return $suc->RunCmdResult;
    }

}