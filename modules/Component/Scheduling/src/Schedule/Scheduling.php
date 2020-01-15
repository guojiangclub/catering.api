<?php

namespace GuoJiangClub\Catering\Component\Scheduling\Schedule;

use Illuminate\Console\Scheduling\Schedule;

abstract class Scheduling implements ScheduleContract
{
	protected $schedule;

	public function __construct(Schedule $schedule)
	{
		$this->schedule = $schedule;
	}

}