<?php
/**
 * 错误枚举类
 * @author  wdy
 * @date    2016-01-11
 */

namespace App\XmlParse;

class CEnumError
{

    //执行成功
    public static $Success = 0;
    //执行错误
    public static $Error = 1;
    //参数错误，未明确具体信息
    public static $ParamSpecificError = 10;
    //参数值错误，如身份证号码位数不足
    public static $ParamValueError = 11;
    //缺少参数
    public static $LackParam = 12;
    //参数类型错误，如费用传递的字母
    public static $ParamType = 13;
    //数据错误，未明确具体信息
    public static $DataSpecificError = 20;
    //数据错误，未明确具体信息
    public static $ConnectDBError = 21;
    //添加数据失败
    public static $AddDataError = 22;
    //查询数据失败
    public static $SelectDataError = 23;
    //更新数据失败
    public static $UpdateDataError = 24;
    //删除数据失败
    public static $DeleteDataError = 25;
    //网络错误，未明确具体信息
    public static $NetWorkSpecificError = 30;
    // ErrorCode类型转换错误
    public static $AnalyticErrorCode = 31;
    //XML解析异常
    public static $AnalyticError = 32;
    //其他错误
    public static $OtherError = 90;
    //异常
    public static $Exception = 99;


}
