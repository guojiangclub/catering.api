<?php

namespace GuoJiangClub\Catering\Server\Channels;

abstract class BaseChannel
{
	const TYPE = '';

	public function getName()
	{
		return static::TYPE;
	}
}