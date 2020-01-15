<?php

namespace GuoJiangClub\Catering\Server\Http\Controllers;

use GuoJiangClub\Catering\Backend\Models\Banners;

class BannersController extends Controller
{
	public function list()
	{
		$banners = Banners::where('status', 1)->get();

		return $this->success($banners);
	}

	public function popup()
	{
		$img = settings('homepage_swal_img');

		return $this->success(['img' => $img]);
	}
}