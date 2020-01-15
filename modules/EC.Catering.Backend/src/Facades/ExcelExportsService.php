<?php

namespace GuoJiangClub\EC\Catering\Backend\Facades;

use Illuminate\Support\Facades\Facade;

class ExcelExportsService extends Facade
{
	protected static function getFacadeAccessor()
	{
		return 'ExcelExportsService';
	}
}