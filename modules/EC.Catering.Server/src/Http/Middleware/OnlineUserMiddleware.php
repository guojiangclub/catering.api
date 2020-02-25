<?php

namespace ElementVip\Server\Http\Middleware;


use Closure;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Redis;

class OnlineUserMiddleware
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @return mixed
     */
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
        try {

            $seckill_max_online_user = settings('seckill_max_online_user') ? settings('seckill_max_online_user') : 0;

            if ($seckill_max_online_user) {
                // 获取获取IP
                $ip = Request()->getClientIp();

                $cacheKey = 'online_user_count';

                collect_cache([$ip => 1], $cacheKey, 1);
            }

        } catch (\Exception $e) {
            \Log::info('缓存统计IP失败');
        }

        /*try{

            $seckill_max_online_user=settings('seckill_max_online_user')?settings('seckill_max_online_user'):0;

            if($seckill_max_online_user){
                // 获取获取IP
                $ip = Request()->getClientIp();
                $time=date('H:i',Carbon::now()->timestamp);
                $key=$time.$ip;
                $online=$time.'_online';
                if(cache($key)==null){
                    cache([$key => $ip], Carbon::now()->addMinute());
                    if(cache($online)==null){
                        cache([$online => 1], Carbon::now()->addMinute());
                    }else{
                        \Cache::increment($online,1);
                    }
                }
              Redis::expire($key,60);
            }

        }catch (\Exception $e){
            \Log::info('缓存统计IP失败');
        }*/

        return $next($request);
    }

}