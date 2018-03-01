<?php
/**
 * 模型
 * User: wdy
 * Date: 2016/03/01
 * Time: 21:36
 */

namespace App\Models;

class ScanCode extends Base
{
    const SCAN_CODE_ACTION_AGC = 'AGC';

    protected $table = 'tab_scan_code';

    public function scopeValid($query)
    {
        return $query->whereRaw('cl_ValidTime > now()');
    }


}