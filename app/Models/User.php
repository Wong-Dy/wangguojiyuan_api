<?php
/**
 * model
 * User: wdy
 * Date: 2016/2/2
 * Time: 9:53
 */

namespace App\Models;

use App\Util\Comm;
use DB, Exception;

class User extends Base
{
    protected $table = 'wdy_users';
    protected $primaryKey = "user_id";

    public function system()
    {
        return $this->hasOne('App\Models\UserSystem', 'cl_UserId', 'user_id');
    }

    public function gameinfo()
    {
        return $this->hasOne('App\Models\UserGameInfo', 'cl_UserId', 'user_id');
    }

    public function wxuser()
    {
        return $this->hasOne('App\Models\WXUser', 'ecuid', 'user_id');
    }

    public function getHeadImg()
    {
        return empty($this->headimg) ? '' : CUSTOM_API_APP_HOST . $this->headimg;
    }

    public function getGameName(){
        $gameinfo = $this->gameinfo;
        return null == $gameinfo ? '' : $gameinfo->cl_NickName;
    }

    //创建新帐号
    public static function createAccount()
    {
        $account = self::getAutoAccount();

        for ($i = 0; $i < 15; $i++) {
            if (self::where('user_name', $account)->count() > 0)
                $account = self::getAutoAccount();
            else
                return $account;
        }
        return str_shuffle(Comm::make_rand(8));
    }

    //生成帐号
    private static function getAutoAccount()
    {
        $id_len = 2;
        $id = self::max('user_id') + 1;
        if (strlen($id) < $id_len)
            $id = $id . rand(1000, 9999);

        if (strlen($id) > 9)
            return str_shuffle($id);
        $rand = rand(10, 99);
        return str_shuffle($rand . $id);
    }
}