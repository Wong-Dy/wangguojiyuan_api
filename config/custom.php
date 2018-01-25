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
    'captcha' => '赛狐：您的验证码为 @Code ，有效时间10分钟，谢谢您的使用。 ',

    'userNoticeMsgList' => [
        0 => '王国语音通知，您的保护盾马上到期，请及时处理。',
    ],

    'userNoticeMsgPriceList' => [
        0 => 0.2,
    ]

];