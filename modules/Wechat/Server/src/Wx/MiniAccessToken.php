<?php

namespace GuoJiangClub\EC\Catering\Wechat\Server\Wx;

/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/3/20
 * Time: 16:23
 */

use GuoJiangClub\EC\Catering\Wechat\Server\Overtrue\AccessToken as CoreAccessToken;

class MiniAccessToken extends CoreAccessToken
{
    protected $prefix = 'ibrand.common.mini.program.access_token.';

}