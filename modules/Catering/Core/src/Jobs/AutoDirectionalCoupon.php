<?php
namespace GuoJiangClub\Catering\Core\Jobs;

use GuoJiangClub\Catering\Core\Processor\DirectionalCouponProcessor;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

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
        $this->gift = $id;
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
