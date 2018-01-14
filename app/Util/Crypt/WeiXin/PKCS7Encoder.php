<?php
/**
 * 工具类
 * User: wdy
 * Date: 2017/11/14
 * Time: 10:58
 */

namespace App\Util\Crypt\WeiXin;

use Exception;

/**【填充】**（全过程没用到）
 * 用于AES的PKCS#7填充
 * 提供基于PKCS#7算法(加解密接口)
 * 对称解密使用的算法为 AES-128-CBC，数据采用PKCS#7填充。
 */
class PKCS7Encoder
{
    //块大小为16个字节
    public static $block_size = 16;
    /**
     * 对需要加密的明文进行填充补位
     * @param $text 需要进行填充补位操作的明文
     * @return 补齐明文字符串
     */
    function encode( $text )
    {
        $block_size = PKCS7Encoder::$block_size;
        $text_length = strlen( $text );
        //计算需要填充的位数
        $amount_to_pad = PKCS7Encoder::$block_size - ( $text_length % PKCS7Encoder::$block_size );
        if ( $amount_to_pad == 0 ) {
            $amount_to_pad = PKCS7Encoder::block_size;
        }
        //获得补位所用的字符
        $pad_chr = chr( $amount_to_pad );
        $tmp = "";
        for ( $index = 0; $index < $amount_to_pad; $index++ ) {
            $tmp .= $pad_chr;
        }
        return $text . $tmp;
    }
    /**【去除填充】**
     * 对解密后的明文进行补位删除
     * @param decrypted 解密后的明文
     * @return 删除填充补位后的明文
     */
    function decode($text)
    {
        $pad = ord(substr($text, -1));
        if ($pad < 1 || $pad > 32) {
            $pad = 0;
        }
        return substr($text, 0, (strlen($text) - $pad));
    }
}