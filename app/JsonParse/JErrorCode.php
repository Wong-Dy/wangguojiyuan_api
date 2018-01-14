<?php
/**
 * 错误枚举类
 * @author  wdy
 * @date    2017-03-19
 */

namespace App\JsonParse;

class JErrorCode
{
    const SUCCESS = 1;  //执行成功
    const ERROR = 0;    //执行失败
    const PARAM_ERROR = 10001;  //参数值错误，如身份证号码位数不足
    const LACK_PARAM_ERROR = 10002; //缺少参数
    const INVALID_PARAM_ERROR = 10003; //无效参数
    const INVALID_CMD_ERROR = 10004; //无效指令
    const NOT_FOUND_ERROR = 10005;  //未找到
    const INVALID_Aut_ERROR = 10006; //无权限
    const OTHER_ERROR = 10090;  //其他错误
    const EXCEPTION_ERROR = 10099;  //异常错误


    const CUSTOM_USER_NOT_FOUND = 10110;  //用户未找到
    const CUSTOM_SELECT_NOT_FOUND = 10111;  //查询不存在
    const CUSTOM_ADD_ERROR = 10112;  //添加失败
    const CUSTOM_UPDATE_ERROR = 10113;  //更新失败
    const CUSTOM_ILLEGAL_OPERATION_ERROR = 10114;  //非法操作
    const CUSTOM_SELECT_HAVE_EXISTED = 10115;//查询数据已经存在
    const CUSTOM_USER_PASSWORD_ERROR = 10116;//密码错误
    const CUSTOM_VERIFY_ERROR = 10116;//验证失败
    const CUSTOM_VERIFY_TIMEOUT_ERROR = 10116;//验证超时
    const CUSTOM_PHONE_FORMAT_ERROR = 10117;//手机号码格式错误

    const SUPPLIER_ID_NOT_ERROR = 10200;//未找到supplierID
    const SUPPLIER_GOODS_NOT_ERROR = 10201;//未找到商品
    const SUPPLIER_GOODS_PERFECT_ERROR = 10202; //请完善盘点商品信息
    const SUPPLIER_GOODS_EDIT_ERROR = 10203; //请编辑要盘点的商品
    const SUPPLIER_NOT_FOUND_ERROR = 10204; //店铺不存在

    const INTERFACE_REQUEST_ERROR = 20201; //接口请求失败
    const INTERFACE_REQUEST_PHONE_MSG_ERROR = 20202; //短信接口发送失败

    const WX_SESSIONID_NULL_ERROR = 30101; //微信小程序会话ID为空
    const WX_SESSIONID_NOT_VALID_ERROR = 30102; //微信小程序会话ID无效
    const WX_USER_NOT_BIND_PHONE_ERROR = 30103; //微信用户未绑定手机号码
    const WX_USER_INFO_NOT_FOUND_ERROR = 30104; //微信小程序用户信息不存在

    const ERROR_INFO = [
        self::SUCCESS => 'success',
        self::ERROR => 'error',
        self::PARAM_ERROR => 'param value error',
        self::LACK_PARAM_ERROR => 'lack param',
        self::OTHER_ERROR => 'other error',
        self::EXCEPTION_ERROR => 'exception error',
        self::INVALID_CMD_ERROR => 'invalid cmd',
        self::NOT_FOUND_ERROR => 'Not found',
        self::INVALID_PARAM_ERROR => 'invalid param',

        self::CUSTOM_USER_NOT_FOUND => '用户未找到',
        self::CUSTOM_SELECT_NOT_FOUND => '查询为空',
        self::CUSTOM_ADD_ERROR => '添加失败',
        self::CUSTOM_UPDATE_ERROR => '更新失败',
        self::CUSTOM_ILLEGAL_OPERATION_ERROR => '非法操作',
        self::CUSTOM_SELECT_HAVE_EXISTED => '数据已经存在',
        self::CUSTOM_USER_PASSWORD_ERROR => '密码错误',
        self::CUSTOM_VERIFY_ERROR => '校验失败',
        self::CUSTOM_VERIFY_TIMEOUT_ERROR => '校验超时',
        self::CUSTOM_PHONE_FORMAT_ERROR => '手机号码格式不正确',

        self::SUPPLIER_ID_NOT_ERROR => '未找到supplierID',
        self::SUPPLIER_GOODS_NOT_ERROR => '未找到商品',
        self::SUPPLIER_GOODS_PERFECT_ERROR => '请完善盘点商品信息',
        self::SUPPLIER_GOODS_EDIT_ERROR => '请编辑要盘点的商品',
        self::SUPPLIER_NOT_FOUND_ERROR => '店铺不存在',


        self::INTERFACE_REQUEST_ERROR => '接口请求失败',
        self::INTERFACE_REQUEST_PHONE_MSG_ERROR => '短信发送失败',


        self::WX_SESSIONID_NULL_ERROR => 'sessionId is null',
        self::WX_SESSIONID_NOT_VALID_ERROR => 'sessionId not valid',
        self::WX_USER_NOT_BIND_PHONE_ERROR => '微信用户未绑定手机号码',
        self::WX_USER_INFO_NOT_FOUND_ERROR => '微信小程序用户信息不存在，请重新启动小程序授权',

    ];
}
