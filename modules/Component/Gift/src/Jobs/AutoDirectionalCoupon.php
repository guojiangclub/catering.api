<?php

namespace GuoJiangClub\Catering\Component\Gift\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use GuoJiangClub\Catering\Component\Gift\Processor\DirectionalCouponProcessor;

class AutoDirectionalCoupon implements ShouldQueue
{
	use InteractsWithQueue, Queueable, SerializesModels;

	/**
	 * Create a new job instance.
	 */

	protected $gift;
	protected $user_id;

	public function __construct($id, $user_id)
	{
		$this->gift    = $id;
		$this->user_id = $user_id;
	}

	/**
	 * Execute the job.
	 *
	 * @return void
	 */
	public function handle(DirectionalCouponProcessor $DirectionalCouponProcessor)
	{
		$DirectionalCouponProcessor->DirectionalCoupon($this->gift, $this->user_id);
	}

	public function failed(\Exception $e)
	{

	}

}
