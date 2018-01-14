<?php
/**
 * 全局类
 * User: wdy
 * Date: 2016/2/2
 * Time: 11:10
 */

namespace App\Util;


class CGlobal
{
    /**
     * 认证类型(后台)
     */
    const AUTH_ADMIN = 'backend';
    /**
     * 认证类型(企业)
     */
    const AUTH_COMPANY = 'company';

    /**
     * 序列号生成规则(默认)
     */
    const SEQNO_DEFAULT = 'default';
    const SEQNO_USER_ACCOUNT_TRADE_NO = 'user_account_trade_no';
    const SEQNO_USER_ORDER_TRADE_NO = 'user_order_trade_no';


    const WEIXIN_PAY_RECHARGE_FLAG = 1;    //充值
    const WEIXIN_PAY_ORDER_FLAG = 2;   //订单

    /**
     * 验证码session
     */
    const SESSION_QRCODE = 'CheckCode';

    /**
     * 分页显示条数
     */
    const PAGE_SIZE = 12;

    const GOOGLE2FA_USER_PAY_PRIVATE_KEY = 'VQDOZ2X7DDQUZT3A';
    const GOOGLE2FA_USER_PAY_TOKEN_PREFIX = '21';


}