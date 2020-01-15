<?php

namespace GuoJiangClub\Catering\Component\Order\Jobs;

use GuoJiangClub\Catering\Component\Order\Models\Order;
use GuoJiangClub\Catering\Component\Order\Processor\OrderProcessor;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class AutoCancelOrder implements ShouldQueue
{
	use InteractsWithQueue, Queueable, SerializesModels;

	protected $order;

	/**
	 * Create a new job instance.
	 *
	 * @return void
	 */
	public function __construct(Order $order)
	{
		$this->order = $order;
	}

	/**
	 * Execute the job.
	 *
	 * @return void
	 */
	public function handle(OrderProcessor $orderProcessor)
	{
		$orderProcessor->cancel($this->order);
	}
}
