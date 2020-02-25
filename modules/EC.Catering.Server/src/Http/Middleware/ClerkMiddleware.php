<?php

namespace ElementVip\Server\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;
use Auth;
use Dingo\Api\Http\Response;

class ClerkMiddleware
{
    /**
     * The Guard implementation.
     *
     * @var Guard
     */
    protected $auth;

    /**
     * Create a new filter instance.
     *
     * @param  Guard  $auth
     * @return void
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($this->auth->check()) {
            if(!auth('clerk')->user()->status){
                return new Response(['status' => false
                    , 'code' => 405
                    , 'message' => '账号被禁用'
                    , 'data' => '']);
            }
        }

        return $next($request);
    }
}
