<?php

namespace GuoJiangClub\Catering\Backend\Listeners;

use GuoJiangClub\Catering\Backend\Models\ClerkBind;
use Predis\Client;

class ClerkBindEventListener
{
	public function onClerkScan(array $params, $userInfo)
	{
		if (!isset($params['app_id']) || !isset($params['openid'])) {
			return;
		}

		if (isset($userInfo->user_info_list) && count($userInfo->user_info_list) > 0) {
			$user = $userInfo->user_info_list[0];
			$user = collect($user)->toArray();
			$data = [
				'app_id'          => $params['app_id'],
				'subscribe'       => $user['subscribe'],
				'openid'          => $user['openid'],
				'nick_name'       => $user['nickname'],
				'sex'             => $user['sex'],
				'city'            => $user['city'],
				'province'        => $user['province'],
				'country'         => $user['country'],
				'language'        => $user['language'],
				'headimgurl'      => $user['headimgurl'],
				'subscribe_time'  => $user['subscribe_time'],
				'unionid'         => isset($user['unionid']) ? $user['unionid'] : '',
				'remark'          => $user['remark'],
				'groupid'         => $user['groupid'],
				'tagid_list'      => json_encode($user['tagid_list']),
				'subscribe_scene' => $user['subscribe_scene'],
				'qr_scene'        => $user['qr_scene'],
				'qr_scene_str'    => $user['qr_scene_str'],
			];

			$clerkBind = ClerkBind::where('openid', $user['openid'])->first();
			if (!$clerkBind) {
				ClerkBind::create($data);
			} else {
				ClerkBind::where('id', $clerkBind->id)->update($data);
			}

			$options = [
				'host'     => env('REDIS_HOST'),
				'port'     => env('REDIS_PORT'),
				'password' => env('REDIS_PASSWORD'),
			];
			$client  = new Client($options);
			$client->set('userInfo', json_encode(['nick_name' => $user['nickname'], 'headimgurl' => $user['headimgurl'], 'openid' => $user['openid']]));
		}
	}

	public function subscribe($events)
	{
		$events->listen(
			'st.clerk.scan.bind',
			'GuoJiangClub\Catering\Backend\Listeners\ClerkBindEventListener@onClerkScan'
		);
	}
}