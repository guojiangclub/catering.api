<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016-08-24
 * Time: 12:19
 */

namespace ElementVip\Server\Http\Middleware;

use Closure;
use Response;

class EnableCrossRequestMiddleware
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->is('api/*')) {
            $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : false;

            if ($referer) {
                $urls = parse_url($referer);
                $url = $urls['scheme'] . '://' . $urls['host'];
                isset($urls['port']) ? $url .= ':' . $urls['port'] : '';
            } else {
                $url = '*';
            }

            header("Access-Control-Allow-Origin: " . $url);//跨域访问
            header("Access-Control-Allow-Credentials: true ");
            header("Access-Control-Allow-Methods: OPTIONS, GET, POST, PUT");
            header("Access-Control-Allow-Headers: Content-Type, Depth, User-Agent, X-File-Size, X-Requested-With, If-Modified-Since, X-File-Name, Cache-Control");
        }

        return $next($request);
    }

}