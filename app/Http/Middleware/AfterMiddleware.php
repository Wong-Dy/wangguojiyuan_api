<?php
/**
 * 后置中间件
 * User: wdy
 * Date: 2016/2/2
 * Time: 15:51
 */

namespace App\Http\Middleware;

use Closure;

class AfterMiddleware {

    public function handle($request, Closure $next)
    {
        $response = $next($request);


        return $response;
    }
}