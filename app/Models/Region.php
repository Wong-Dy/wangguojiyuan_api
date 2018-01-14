<?php
/**
 * Created by PhpStorm.
 * User: jiangchuanyi
 * Date: 2017/09/10
 * Time: 10:42
 */

namespace App\Models;


class Region extends Base
{
    protected $table = 'tab_region';
    protected $primaryKey = 'cl_Id';

    public function scopeType($q, $type)
    {
        $q->where('cl_Type', $type);
    }

    public static function getProvinceList()
    {
        return self::type(1)->get();
    }

    public static function getCityList($parentId = 0)
    {
        return self::type(2)->where('cl_Parent', $parentId)->get();
    }

    public static function getDistrictList($parentId = 0)
    {
        return self::type(3)->where('cl_Parent', $parentId)->get();
    }

    public static function getDefaultCountryId()
    {
        return 100000;
    }

    public static function getRegionName($id)
    {
        return self::select('cl_FullName')->where('cl_Id', $id)->pluck('cl_FullName')->first();
    }

    /*
     * 获取市区
     * $dataModel array()
     *
    */
    public static function recipientAddress($dataModel)
    {

        $model = self::where('cl_Id', $dataModel['city'])
            ->orWhere('cl_Id', $dataModel['district'])
            ->pluck('cl_FullName');
        return @($model['0'] . $model['1']);

    }

    public static function getAddressArray($regionArr = [], $province = 0, $city = 0, $district = 0)
    {
        if (!empty($regionArr)) {
            if (isset($regionArr->province))
                $province = $regionArr->province;
            if (isset($regionArr->city))
                $city = $regionArr->city;
            if (isset($regionArr->district))
                $district = $regionArr->district;
        }

        $list = self::select('cl_FullName')->whereIn('cl_Id', [$province, $city, $district])
            ->pluck('cl_FullName');

        if (count($list) == 1)
            return [$list[0], '', ''];
        if (count($list) == 2)
            return [$list[0], ''];
        if (count($list) == 3)
            return $list;

        return ['', '', ''];
    }

    public static function getAddress($regionArr = [], $province = 0, $city = 0, $district = 0)
    {
        if (!empty($regionArr)) {
            if (isset($regionArr->province))
                $province = $regionArr->province;
            if (isset($regionArr->city))
                $city = $regionArr->city;
            if (isset($regionArr->district))
                $district = $regionArr->district;
        }

        $list = self::select('cl_FullName')->whereIn('cl_Id', [$province, $city, $district])
            ->pluck('cl_FullName');
        return implode('', $list);
    }

}