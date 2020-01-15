<?php

namespace GuoJiangClub\Catering\Backend\Schedule;

use GuoJiangClub\Catering\Component\Scheduling\Schedule\Scheduling;

class StatisticsResultSchedule extends Scheduling
{
	public function schedule()
	{
		$this->schedule->call(function () {
			\Log::info('进入统计结果通知定时任务');

			event('st.send.statistics.message');
		})->dailyAt('11:00');
	}
}