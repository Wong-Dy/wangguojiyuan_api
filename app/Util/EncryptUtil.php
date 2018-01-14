<?php
/**
 * 加密方法类
 * @author  wdy
 * @date    2016-01-11
 */

namespace App\Util;

use App\Util\Crypt\AES;
use App\Util\Crypt\WeiXin\WXBizDataCrypt;

class EncryptUtil
{
    //==========================================
    // ASCII加密
    // @param $data  数据
    // @param $key   密钥
    // @param $type  默认encrypt|decrypt
    //==========================================
    public static function ASCII($data, $key, $type = 'encrypt')
    {
        $key = md5($key);
        $x = 0;

        if ($type == 'decrypt')
            $data = base64_decode($data);   //base64解密

        $len = strlen($data);  //对象长度
        $l = strlen($key);   //key长度
        $char = "";
        $str = "";

        for ($i = 0; $i < $len; $i++) {
            if ($x == $l) {
                $x = 0;
            }
            $char .= $key{$x};    //x位置的key内容给char
            $x++;
        }

        if ($type == 'encrypt') {
            for ($i = 0; $i < $len; $i++) {
                $str .= chr(ord($data{$i}) + ord($char{$i}));   //chr()从指定的 ASCII值返回字符。ord()返回字符串第一个字符的 ASCII值。
            }
            return base64_encode($str);   //base64加密
        }
        if ($type == 'decrypt') {
            for ($i = 0; $i < $len; $i++) {
                $str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
            }
            return $str;
        }

        if (!in_array($type, array('encrypt', 'decrypt')))
            return "type类型参数有误！";

    }

    //==========================================
    // AES加密的ECB模式k128
    // @param $data  数据
    // @param $key   密钥
    // @param $type  默认encrypt|decrypt
    //==========================================
    public static function AES_ECB($data, $key, $type = 'encrypt')
    {

        if ($type == 'encrypt') {
            $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB), MCRYPT_RAND);
            $data = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data, MCRYPT_MODE_ECB, $iv);
            return base64_encode($data); //加密base64_encode
        }
        if ($type == 'decode') {
            $data = base64_decode($data);  //base64_decode
            $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB), MCRYPT_RAND);
            return mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $data, MCRYPT_MODE_ECB, $iv);
        }
    }

    //==========================================
    // AES加密的CBC模式k128
    // @param $data  数据
    // @param $key   密钥
    // @param $type  默认encrypt|decrypt
    //==========================================
    public static function AES_CBC($data, $key, $type = 'encrypt')
    {

        if ($type == 'encrypt') {
            $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB), MCRYPT_RAND);
            $data = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data, MCRYPT_MODE_CBC, $iv);
            return base64_encode($data); //加密base64_encode
        }
        if ($type == 'decode') {
            $data = base64_decode($data);  //base64_decode
            $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB), MCRYPT_RAND);
            return mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $data, MCRYPT_MODE_CBC, $iv);
        }

    }

    //==========================================
    // AES加密的ECB模式PKCS5Padding
    // @param $data  数据
    // @param $key   密钥 只能为24位字符
    // @param $type  默认encrypt|decrypt
    //==========================================
    public static function AES_ECB_PKCS5($data, $key, $type = 'encrypt')
    {

        if ($type == 'encrypt') {
            $size = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB);
            $data = self::pkcs5_pad($data, $size);
            $td = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_ECB, '');
            $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
            mcrypt_generic_init($td, $key, $iv);
            $data = mcrypt_generic($td, $data);
            mcrypt_generic_deinit($td);
            mcrypt_module_close($td);
            $data = base64_encode($data);
            return $data;
        }
        if ($type == 'decode') {
            $decrypted = mcrypt_decrypt(
                MCRYPT_RIJNDAEL_128,
                $key,
                base64_decode($data),
                MCRYPT_MODE_ECB
            );

            $dec_s = strlen($decrypted);
            $padding = ord($decrypted[$dec_s - 1]);
            $decrypted = substr($decrypted, 0, -$padding);
            return $decrypted;
        }
    }

    public static function AES_ECB_PKCS7($data, $key, $type = 'encrypt')
    {
        if ($type == 'encrypt') {
            $size = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB);
            $data = self::pkcs7_pad($data, $size);
            $td = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_ECB, '');
            $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
            mcrypt_generic_init($td, $key, $iv);
            $data = mcrypt_generic($td, $data);
            mcrypt_generic_deinit($td);
            mcrypt_module_close($td);
            $data = base64_encode($data);
            return $data;
        }
        if ($type == 'decode') {
            $decrypted = mcrypt_decrypt(
                MCRYPT_RIJNDAEL_128,
                $key,
                base64_decode($data),
                MCRYPT_MODE_ECB
            );

            $decrypted = self::pkcs7Unpad($decrypted);
            return $decrypted;
        }
    }


    //==========================================
    // DES加密的CBC模式
    // @param $data  数据
    // @param $key   密钥 只能为8位字符
    // @param $type  默认encrypt|decrypt
    //==========================================
    public static function DES_CBC($string, $key, $type = 'encrypt')
    {
        if ($type == 'encrypt') {

            $ivArray = array(0x12, 0x34, 0x56, 0x78, 0x90, 0xAB, 0xCD, 0xEF);
            $iv = null;
            foreach ($ivArray as $element)
                $iv .= CHR($element);


            $size = mcrypt_get_block_size(MCRYPT_DES, MCRYPT_MODE_CBC);
            $string = self::pkcs5_pad($string, $size);

            $data = mcrypt_encrypt(MCRYPT_DES, $key, $string, MCRYPT_MODE_CBC, $iv);

            $data = base64_encode($data);
            return $data;

        }
        if ($type == 'decode') {

            $ivArray = array(0x12, 0x34, 0x56, 0x78, 0x90, 0xAB, 0xCD, 0xEF);
            $iv = null;
            foreach ($ivArray as $element)
                $iv .= CHR($element);

            $string = base64_decode($string);

            $result = mcrypt_decrypt(MCRYPT_DES, $key, $string, MCRYPT_MODE_CBC, $iv);
            $result = self::pkcs5Unpad($result);

            return $result;
        }
    }

    //==========================================
    // DES加密ECB模式k128
    // @param $data  数据
    // @param $key   密钥 只能为8位字符
    // @param $type  默认encrypt|decrypt
    //==========================================
    public static function DES_ECB($data, $key, $type = 'encrypt')
    {
        if ($type == 'encrypt') {

            $size = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB);

            $pad = $size - (strlen($data) % $size);
            $input = $data . str_repeat(chr($pad), $pad);

            $td = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_ECB, '');
            $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
            mcrypt_generic_init($td, $key, $iv);
            $data = mcrypt_generic($td, $input);
            mcrypt_generic_deinit($td);
            mcrypt_module_close($td);
            return base64_encode($data);

        }
        if ($type == 'decode') {

            $decrypted = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, base64_decode($data), MCRYPT_MODE_ECB);
            $dec_s = strlen($decrypted);
            $padding = ord($decrypted[$dec_s - 1]);
            $decrypted = substr($decrypted, 0, -$padding);
            return $decrypted;
        }
    }

    //==========================================
    // PHP DES ECB PKCS7 加密程式
    // @param $key 密鑰（八個字元內）
    // @param $data 要加密的明文
    // @param $type  默认encrypt|decrypt
    // @return string 密文
    //==========================================
    public static function DES_ECB_PKCS7($data, $key, $type = 'encrypt')
    {
        if ($type == 'encrypt') {
            // 根據 PKCS#7 RFC 5652 Cryptographic Message Syntax (CMS) 修正 Message 加入 Padding
            $block = mcrypt_get_block_size(MCRYPT_DES, MCRYPT_MODE_ECB);
            $pad = $block - (strlen($data) % $block);
            $data .= str_repeat(chr($pad), $pad);

            // 不需要設定 IV 進行加密
            $passcrypt = mcrypt_encrypt(MCRYPT_DES, $key, $data, MCRYPT_MODE_ECB);
            return base64_encode($passcrypt);
        }
        if ($type == 'decode') {
            // 不需要設定 IV
            $str = mcrypt_decrypt(MCRYPT_DES, $key, base64_decode($data), MCRYPT_MODE_ECB);

            // 根據 PKCS#7 RFC 5652 Cryptographic Message Syntax (CMS) 修正 Message 移除 Padding
            $pad = ord($str[strlen($str) - 1]);
            return substr($str, 0, strlen($str) - $pad);
        }
    }

    //==========================================
    // 3DES加密ECB模式
    // @param $data  数据
    // @param $key   密钥24位
    // @param $type  默认encrypt|decrypt
    //==========================================
    public static function SDES_ECB($string, $key, $type = 'encrypt')
    {

        if ($type == 'encrypt') {
            //加密方法
            $cipher_alg = MCRYPT_TRIPLEDES;
            //初始化向量来增加安全性
            $iv = mcrypt_create_iv(mcrypt_get_iv_size($cipher_alg, MCRYPT_MODE_ECB), MCRYPT_RAND);

            //开始加密
            $encrypted_string = mcrypt_encrypt($cipher_alg, $key, $string, MCRYPT_MODE_ECB, $iv);
            return base64_encode($encrypted_string);//转化成16进制
            //        return $encrypted_string;
        }
        if ($type == 'decode') {
            $string = base64_decode($string);
            //加密方法 
            $cipher_alg = MCRYPT_TRIPLEDES;
            //初始化向量来增加安全性 
            $iv = mcrypt_create_iv(mcrypt_get_iv_size($cipher_alg, MCRYPT_MODE_ECB), MCRYPT_RAND);

            //开始解密 
            $decrypted_string = mcrypt_decrypt($cipher_alg, $key, $string, MCRYPT_MODE_ECB, $iv);
            return trim($decrypted_string);
        }

    }

    /**
     * 3DES加密ECB模式--PKCS7
     * @param $string   数据
     * @param $key  密钥24位
     * @param string $type 默认encrypt|decrypt
     * @return string
     */
    public static function SDES_ECB_PKCS7($string, $key, $type = 'encrypt')
    {

        if ($type == 'encrypt') {
            //加密方法
            $cipher_alg = MCRYPT_TRIPLEDES;
            //初始化向量来增加安全性
            $iv = mcrypt_create_iv(mcrypt_get_iv_size($cipher_alg, MCRYPT_MODE_ECB), MCRYPT_RAND);

            // 根據 PKCS#7 RFC 5652 Cryptographic Message Syntax (CMS) 修正 Message 加入 Padding
            $block = mcrypt_get_block_size(MCRYPT_DES, MCRYPT_MODE_ECB);
            $pad = $block - (strlen($string) % $block);
            $string .= str_repeat(chr($pad), $pad);

            //开始加密
            $encrypted_string = mcrypt_encrypt($cipher_alg, $key, $string, MCRYPT_MODE_ECB, $iv);
            return base64_encode($encrypted_string);//转化成16进制
            //        return $encrypted_string;
        }
        if ($type == 'decode') {
            $string = base64_decode($string);
            //加密方法
            $cipher_alg = MCRYPT_TRIPLEDES;
            //初始化向量来增加安全性
            $iv = mcrypt_create_iv(mcrypt_get_iv_size($cipher_alg, MCRYPT_MODE_ECB), MCRYPT_RAND);

            //开始解密
            $decrypted_string = mcrypt_decrypt($cipher_alg, $key, $string, MCRYPT_MODE_ECB, $iv);

            $pad = ord($decrypted_string[strlen($decrypted_string) - 1]);
            return substr($decrypted_string, 0, strlen($decrypted_string) - $pad);
        }

    }

    public static function RC4($data, $pwd, $type = 'encrypt')//$pwd密钥 $data需加密字符串
    {

        if ($type == 'decode') {
            $data = base64_decode($data);
        }

        $key[] = "";
        $box[] = "";
        $cipher = '';
        $pwd_length = strlen($pwd);
        $data_length = strlen($data);

        for ($i = 0; $i < 256; $i++) {
            $key[$i] = ord($pwd[$i % $pwd_length]);
            $box[$i] = $i;
        }

        for ($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + $key[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }

        for ($a = $j = $i = 0; $i < $data_length; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;

            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;

            $k = $box[(($box[$a] + $box[$j]) % 256)];
            $cipher .= chr(ord($data[$i]) ^ $k);
        }

        if ($type == 'encrypt') {
            $cipher = base64_encode($cipher);//转化成16进制
        }
        return $cipher;
    }

    private static function pkcs5_pad($text, $blocksize)
    {
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }

    private static function pkcs5Unpad($text)
    {
        $pad = ord($text{strlen($text) - 1});
        if ($pad > strlen($text))
            return false;
        if (strspn($text, chr($pad), strlen($text) - $pad) != $pad)
            return false;
        return substr($text, 0, -1 * $pad);
    }

    private static function pkcs7_pad($text, $blocksize)
    {
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text .= str_repeat(chr($pad), $pad);
    }

    private static function pkcs7Unpad($text)
    {
        $pad = ord($text[strlen($text) - 1]);
        return substr($text, 0, strlen($text) - $pad);
    }
}

?>