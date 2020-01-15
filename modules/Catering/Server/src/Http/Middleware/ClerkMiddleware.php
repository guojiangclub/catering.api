<?php

namespace GuoJiangClub\Catering\Server\Http\Middleware;

use Closure;
use Illuminate\Http\Response;

class ClerkMiddleware
{
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request $request
	 * @param  \Closure                 $next
	 *
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		if (!auth('shitang')->check()) {
			return new Response(['status' => false, 'code' => 405, 'message' => '您没有登录']);
		}

		if (auth('shitang')->check()) {
			if (!auth('shitang')->user()->status) {
				return new Response(['status' => false, 'code' => 405, 'message' => '账号已被禁用']);
			}
		}

		return $next($request);
	}
}