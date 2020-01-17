<?php

namespace GuoJiangClub\Catering\AlbumBackend\Listeners;

use GuoJiangClub\Catering\AlbumBackend\Models\Image;

class UploadListeners
{

	public function onUploaded($imgData, $category_id)
	{
		foreach ($imgData as $key => $item) {
			Image::create([
				'url'         => $item['path'],
				'remote_url'  => $item['url'],
				'name'        => $item['name'],
				'category_id' => $category_id,
			]);
		}
	}

	public function subscribe($events)
	{
		$events->listen(
			'image.uploaded',
			'GuoJiangClub\Catering\AlbumBackend\Listeners\UploadListeners@onUploaded'
		);
	}
}