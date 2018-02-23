<?php

define('SL_STATUS','status');
define('SL_WHETHER','whether');
define('SL_SYSTEM','system');

define('SL_ENCRYMODEL','encry_mode');
define('SL_SEX_TYPE','sex_type');
define('SL_NOTICE_TYPE','notice_type');

define('SL_ADVERTISEMENT_TYPE','advertisement_type');

return [

    //是否
    'whether' => array(
        1 => '是',
        0 => '否'
    ),

    //公共状态
    'status' => array(
        1 => '启用',
        0 => '禁用'
    ),

    //公共系统
    'system' => array(
        0 => '全部系统',
        1 => 'Ios',
        2 => 'Android',
    ),

    //加密方式
    'encry_mode' => array(
        'Md5With'     => 'Md5With无密钥',
        'Md5WithHash' => 'Md5WithHash无密钥',
        'ASCII'       => 'ASCII',
        'AES_ECB'     => 'AES_ECB',
        'DES_ECB'     => 'DES_ECB',
        'SDES_ECB'    => '3DES_ECB(php与.net通用)'
    ),

    //性别
    'sex_type' => array(
        1 => '男',
        0 => '女'
    ),

    //公告类型
    'notice_type' => array(
        0 => '个人用户公告',
        1 => '企业用户公告',
        2 => '管理员公告'
    ),

    //广告类型
    'advertisement_type' => array(
        0 => '文字',
        1 => '图片',
        2 => '视频',
        3 => 'web页面',
        4 => 'app页面',
    ),

    'payment' => array(
        1 => '支付宝',
        2 => '微信',
        3 => '余额',
    ),

    'voice_notice_type' => array(
        0 => '开盾到期',
        1 => '遭受集结',
        2 => '维护结束',
    ),
];