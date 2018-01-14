<?php
/**
 * 工具类
 * User: wdy
 * Date: 2017/11/14
 * Time: 10:58
 */

namespace App\Util\Crypt\WeiXin;

use Exception;

/**
 * AES的解密**********************
 *
 * 用于encryptedData
 *
 **********************************/
class Prpcrypt
{
    public $key;

    //构造函数，用密钥初始化
    function __construct($k)
    {
        $this->key = $k;
    }

    /**
     * 对密文进行解密
     * @param string $aesCipher 需要解密的密文
     * @param string $aesIV 解密的初始向量
     * @return string 解密得到的明文
     */
    public function decrypt($aesCipher, $aesIV)
    {
        try {
            //设置为“128位、CBC模式的AES解密”
            $module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
            //用密钥key、初始化向量初始化
            mcrypt_generic_init($module, $this->key, $aesIV);
            //**执行解密**（得到带有PKCS#7填充的半原文，所以要去除填充）
            $decrypted = mdecrypt_generic($module, $aesCipher);
            //清理工作与关闭解密
            mcrypt_generic_deinit($module);
            mcrypt_module_close($module);
        } catch (Exception $e) {
            return array(ErrorCode::$IllegalBuffer, null);
        }
        try {
            //去除补位字符（对半原文去除PKCS#7填充）
            $pkc_encoder = new PKCS7Encoder;
            //最终得到结果$result
            $result = $pkc_encoder->decode($decrypted);
        } catch (Exception $e) {
            //print $e;
            return array(ErrorCode::$IllegalBuffer, null);
        }
        return array(0, $result);
    }
}
