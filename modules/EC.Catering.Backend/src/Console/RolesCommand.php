<?php

/*
 * This file is part of ibrand/catering-backend.
 *
 * (c) iBrand <https://www.ibrand.cc>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GuoJiangClub\EC\Catering\Backend\Console;

use GuoJiangClub\Catering\Component\User\Models\UserRights;
use Illuminate\Console\Command;

class RolesCommand extends Command
{
    protected $signature = 'roles:factory';

    protected $description = 'roles factory.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $rights = UserRights::all();
        if (!$rights->count()) {
            UserRights::create(['name' => '会员价', 'img' => env('APP_URL') . '/assets/backend/images/r-price.png']);
            UserRights::create(['name' => '生日礼券', 'img' => env('APP_URL') . '/assets/backend/images/r-gift.png']);
            UserRights::create(['name' => '积分奖励', 'img' => env('APP_URL') . '/assets/backend/images/r-point.png']);
            UserRights::create(['name' => '充值好礼', 'img' => env('APP_URL') . '/assets/backend/images/r-recharge.png']);
            UserRights::create(['name' => '专属客服', 'img' => env('APP_URL') . '/assets/backend/images/r-service.png']);
            UserRights::create(['name' => '折扣券', 'img' => env('APP_URL') . '/assets/backend/images/r-discount.png']);
            UserRights::create(['name' => '免排队', 'img' => env('APP_URL') . '/assets/backend/images/r-queue.png']);
            UserRights::create(['name' => '积分商城', 'img' => env('APP_URL') . '/assets/backend/images/r-shop.png']);
            UserRights::create(['name' => '纪念礼品', 'img' => env('APP_URL') . '/assets/backend/images/r-se.png']);
            UserRights::create(['name' => '免单特权', 'img' => env('APP_URL') . '/assets/backend/images/r-free.png']);

        }


    }
}
