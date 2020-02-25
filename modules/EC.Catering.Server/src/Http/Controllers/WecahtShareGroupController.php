<?php

namespace GuoJiangClub\EC\Catering\Server\Http\Controllers;

use Illuminate\Http\Request;
use ElementVip\Store\Backend\Model\WecahtGroup;
use ElementVip\Server\Transformers\WecahtShareGroupTransformer;
use Validator;

class WecahtShareGroupController extends Controller
{
	public function store(Request $request)
	{
		$input      = $request->except('file', '_token');
		$rules      = [
			'group_id' => 'required',
			//'user_id'  => 'required',
		];
		$message    = [
			'required' => ':attribute 不能为空',
			'unique'   => ':attribute 已存在',
		];
		$attributes = [
			'group_id' => '群id',
			'user_id'  => '用户id',
		];
		$validator  = Validator::make($input, $rules, $message, $attributes);
		if ($validator->fails()) {
			$warnings     = $validator->messages();
			$show_warning = $warnings->first();

			return $this->failed($show_warning);
		}

		$unique = WecahtGroup::where('group_id', $input['group_id'])->where('user_id', $input['user_id'])->first();
		if ($unique) {
			return $this->success();
		}

		WecahtGroup::create($input);

		return $this->success();
	}

	public function getList()
	{
		$limit = request('limit') ? request('limit') : 15;

		$user_id = request()->user()->id;
		$list    = WecahtGroup::where('user_id', $user_id)->paginate($limit);

		return $this->response()->paginator($list, new WecahtShareGroupTransformer());
	}
}