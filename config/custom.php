<?php
/**
 * 自定义配置
 * User: wdy
 * Date: 2016/4/27
 * Time: 14:03
 */

//站点APP HTTPS地址
define('CUSTOM_HTTPS_SITE_HOST', 'https://app.isaihu.com'); //线上
//define('CUSTOM_HTTPS_APP_HOST', 'http://192.168.1.88:9024'); //线---下

//站点APP HTTP地址
define('CUSTOM_SITE_HOST', 'http://60.191.90.117:9701');    //线上

//接口地址
define('CUSTOM_API_HOST', 'http://60.191.90.117:9702'); //线上
define('CUSTOM_API_APP_HOST', 'https://www.616app.net:9401');   //线上

define('CUSTOM_SMS_URL', 'http://www.epiaogo.com/Sms/Sms.aspx');


define('CUSTOM_USER_NOTICE_MSG_PRICE_LIST_DEFINE', 'userNoticeMsgPriceList');


return [
    'c' => '',

    //发送验证码配置
    'captcha' => '【王国云助手】验证码：@Code ，为了保证安全，打死也不能告诉别人哦。',

    'userNoticeMsgList' => [
        0 => '王国语音通知，您的保护盾马上到期，请及时处理。',
        1 => '王国语音通知，您正在遭遇集结，请火速上线处理。',
        2 => '王国语音通知，官方维护即将结束，请注意上线处理。',
    ],

    'userNoticeMsgPriceList' => [   //语音通知类型价格配置
        0 => 0.2,
        1 => 0.2,
        2 => 0.2,
    ],

    'service_config' => [
        'submail' => [
            'voice_appid' => '20617',
            'voice_appkey' => '880f57a8445f361d111a63d89ac2f19d',

            'message_appid' => '19742',
            'message_appkey' => '455379db2dcd089fe7a74f30ef346c0c',

        ]
    ]

];