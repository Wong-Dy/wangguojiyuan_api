<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/12/15
 * Time: 15:43
 */

namespace App\Jobs\APICmds;

use App\XmlParse\CXMLReturn;
use App\XmlParse\CEnumError;
use Input, Notification, Session, Cache, Exception;

class BaseCmd
{
    protected $exceptionMsg = '服务器宝宝走神了';
    protected $logPath = '/logs/jobs/apicmds/';

    public function __construct()
    {
    }

    public function std()
    {
        return new \stdClass();
    }

    public function error($info, $code = 1)
    {
        return CXMLReturn::error($code, $info);
    }

    public function emptyValid($data, $except)
    {
        foreach ($data as $key => $item) {
            if (false != strstr($except, $item) && empty($item)) {
                return false;
            }
        }
        return true;
    }
}