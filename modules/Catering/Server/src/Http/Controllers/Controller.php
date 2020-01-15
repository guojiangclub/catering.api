<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016-08-19
 * Time: 12:31
 */

namespace GuoJiangClub\Catering\Server\Http\Controllers;

use Dingo\Api\Http\Response;
use Dingo\Api\Routing\Helpers;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

abstract class Controller extends BaseController
{
	use AuthorizesRequests, DispatchesJobs, ValidatesRequests, Helpers;

	/**
	 * @param array $data
	 * @param int   $code
	 * @param bool  $status
	 *
	 * @return Response
	 */
	public function success($data = [], $code = Response::HTTP_OK, $status = true)
	{
		return new Response(['status' => $status
		                     , 'code' => $code
		                     , 'data' => empty($data) ? null : $data]);
	}

	/**
	 * @param      $message
	 * @param int  $code
	 * @param bool $status
	 *
	 * @return mixed
	 */
	public function failed($message, $code = Response::HTTP_BAD_REQUEST, $status = false)
	{
		return new Response(['status'    => $status
		                     , 'code'    => $code
		                     , 'message' => $message]
		);
	}

	public function replaceCdnUrl($img)
	{
		if (!$img || empty($img)) {
			return '';
		}

		if (is_string($img) && str_contains($img, config('ibrand.backend.disks.admin.url')) && !str_contains($img, env('CDN_WANYOUJISHI_URL'))) {
			return str_replace(config('ibrand.backend.disks.admin.url'), env('CDN_WANYOUJISHI_URL'), $img);
		}

		if (is_array($img)) {
			foreach ($img as &$item) {
				if (str_contains($item, config('ibrand.backend.disks.admin.url')) && !str_contains($item, env('CDN_WANYOUJISHI_URL'))) {
					$item = str_replace(config('ibrand.backend.disks.admin.url'), env('CDN_WANYOUJISHI_URL'), $item);
				}
			}

			return $img;
		}

		return $img;
	}
}