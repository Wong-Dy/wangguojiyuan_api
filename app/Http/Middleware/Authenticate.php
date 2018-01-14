<?php

namespace App\Http\Middleware;

use App\Util\CGlobal;
use App\Util\PublicJS;
use App\Util\Tool;
use App\Util\UserObject;
use Closure;

class Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @param  string|null $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $url = explode("?", $_SERVER["REQUEST_URI"])[0];
        $arrAction = explode("/", $url);
        if (count($arrAction) > 1) {
            switch ($arrAction[1]) {
                case CGlobal::AUTH_ADMIN;
                    if (!UserObject::adminAuth())
                        return redirect('/backend/auth/login');

                    if (!self::adminVerify())
                        return PublicJS::msgBoxRedirect("无权限访问", "/backend/home");

                    break;
                case CGlobal::AUTH_COMPANY;

                    break;
            }
        }
        return $next($request);
    }

    public function adminVerify()
    {
        $url = explode("?", $_SERVER["REQUEST_URI"])[0];

        if (request('_method') == 'PUT')
            $url .= '/edit';
        
        if (UserObject::adminVerifyRole($url) !== true) {
            if ($url != '/backend/home') {
                return false;
            }
        }

        return true;
    }

}
