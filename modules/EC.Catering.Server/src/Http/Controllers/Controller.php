<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016-08-19
 * Time: 12:31
 */

namespace ElementVip\Server\Http\Controllers;

use Dingo\Api\Http\Response;
use Dingo\Api\Routing\Helpers;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, Helpers;

    public function api($data = array(), $status = true, $code = 200, $message = '')
    {
        return new Response(['status' => $status
            , 'code' => $code
            , 'message' => $message
            , 'data' => (count($data) == 0 OR empty($data)) ? null : $data]);
    }

    public function success($data = array(), $code = Response::HTTP_OK, $status = true)
    {
        return new Response(['status' => $status
            , 'code' => $code
            , 'data' => (count($data) == 0 OR empty($data)) ? null : $data]);
    }

    /**
     * @param $message
     * @param int $code
     * @param bool $status
     * @return mixed
     */
    public function failed($message, $code = Response::HTTP_BAD_REQUEST, $status = false)
    {
        return new Response(['status' => $status
                , 'code' => $code
                , 'message' => $message]
        );
    }
}