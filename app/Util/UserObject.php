<?php
/**
 * 用户对象操作
 * User: wdy
 * Date: 2016/2/2
 * Time: 13:29
 */

namespace App\Util;

use App\Models\AdminGroup;
use App\Models\Menu;
use Session;
use Exception;

class UserObject
{
    const USER_ADMIN = 'Admin';
    const USER_COMPANY = 'Company';
    const USER_PERSON = 'Person';

    //region 管理员用户实体操作

    /**
     * 管理员登录
     * @param $Model
     */
    public static function adminLogin($Model)
    {
        self::companyLogout();
        self::personLogout();
        Session::put(self::USER_ADMIN, $Model);
    }

    /**
     * 获取管理员实体
     * @return bool
     */
    public static function getAdminUser()
    {
        if (!Session::has(self::USER_ADMIN) || empty(Session::get(self::USER_ADMIN)))
            return false;
        return Session::get(self::USER_ADMIN);
    }

    /**
     * 管理员注销
     */
    public static function adminLogout()
    {
        Session::forget(self::USER_ADMIN);
    }

    /**
     * 管理员认证
     * @return bool
     */
    public static function adminAuth()
    {
        if (!Session::has(self::USER_ADMIN) || empty(Session::get(self::USER_ADMIN)))
            return false;
        return true;
    }

    /**
     * 页面访问权限判断
     * @param $url
     * @return bool
     */
    public static function adminVerifyRole($url)
    {
        try {

            if (!self::adminIsVerifyRole($url, self::GetAdminUser()->cl_Roles))
                return false;
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * url访问权限判断
     * @param $url
     * @param $role
     * @return bool
     */
    public static function adminIsVerifyRole($url, $role)
    {
        if ($role == "")
            return true;
        $url = trim($url);

        $authority = AdminGroup::valid()->where('cl_Id', $role)->pluck('cl_Authority');

        if (Menu::whereIn('cl_Id', explode(',', $authority))->where('cl_Url', $url)->count() > 0)
            return true;

        $urls = Menu::whereIn('cl_Id', explode(',', $authority))->lists('cl_Url');
        if (empty($urls) || count($urls) == 0)
            return false;

        foreach ($urls as $item) {
            $arr1 = explode("/", $url);
            $arr2 = explode("/", $item);
            if (count($arr2) != count($arr1))
                continue;

            for ($i = 0; $i < count($arr2); $i++) {
                if ($arr2[$i] == '?') {
                    $arr2[$i] = $arr1[$i];
                }
            }

            $str1 = implode('/', $arr1);
            $str2 = implode('/', $arr2);
            if ($str1 == $str2)
                return true;
        }
        return false;
    }

    //endregion

    /**
     * 企业注销
     */
    public static function companyLogout()
    {
        Session::forget(self::USER_COMPANY);
    }

    /**
     * 个人注销
     */
    public static function personLogout()
    {
        Session::forget(self::USER_PERSON);
    }
}