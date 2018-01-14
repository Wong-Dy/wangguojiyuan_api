<?php
/**
 * Socket发送数据类
 * User: wdy
 * Date: 2016/1/11
 * Time: 17:39
 */

namespace App\Util;

use Exception;

class SocketClient
{
    public $service_port;
    public $address;

    /**
     * 发送刷内存指令
     *
     * @param $strMsg
     * @return int|string
     */
    public function startClient($strMsg)
    {
        $ret = '';
        try {
            $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
            if ($socket < 0)
                return 0;

            $result = socket_connect($socket, $this->address, $this->service_port);
            if ($result < 0)
                return 0;

            $in = $strMsg;

            if (!socket_write($socket, $in, strlen($in)))
                return 0;

            while ($out = socket_read($socket, 81920)) {
                //echo "接收服务器回传信息成功！\n";
                //echo "接受的内容为:",$out;
                $ret = $out;
                break;
            }

            socket_close($socket);
        } catch (Exception $e) {
            return $e->getMessage();
        }
        return $ret;
    }

    public function startClientForCamera($strMsg)
    {
        $ret = '';
        try {
            $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
            if ($socket < 0)
                return 0;

            $result = socket_connect($socket, $this->address, $this->service_port);
            if ($result < 0)
                return 0;

            $in = $strMsg;

            if (!socket_write($socket, $in, strlen($in)))
                return 0;

            $json = '';
            while ($out = @socket_read($socket, 2048, PHP_NORMAL_READ)) {

                if (!$out) {
                    break;
                }
                $ret .= $out;

                $result = explode(self::ascii('\n\n'), $ret);
                if (count($result) == 2) {
                    //获取\n\n后面的字符串
                    $json = stristr($ret, self::ascii('\n\n'));
                }
                //匹配json是否有效
                if (null != json_decode($json))
                    break;
            }
            socket_close($socket);
        } catch (Exception $e) {
            return $e->getMessage();
        }
        return $ret;
    }

    private static function ascii($str)
    {
        $ret = '';
        switch ($str) {
            case '\n':
                $ret = chr(10);
                break;
            case '\n\n':
                $ret = chr(10) . chr(10);
                break;
        }
        return $ret;
    }

}