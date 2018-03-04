<?php

/**
 * App版本实体
 * User: wdy
 * Date: 2017/03/13
 * Time: 10:49
 */

namespace App\Models;

class AppVersion extends Base
{
    protected $table = 'tab_appversion';

    public function getSystemDesc()
    {
        return isset(getSelectList(SL_SYSTEM)[$this->cl_System]) ? getSelectList(SL_SYSTEM)[$this->cl_System] : "未知";
    }

    public function getUrl()
    {
        if (strpos(strtoupper($this->cl_FilePath), strtoupper("http://")) !== false)
            return $this->cl_FilePath;
        else
            return CUSTOM_SITE_HOST . $this->cl_FilePath;
    }
}