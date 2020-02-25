<?php

namespace ElementVip\Server\Http\Controllers;

use ElementVip\Component\Seckill\Repositories\SeckillRepository;
use ElementVip\Component\Seckill\Repositories\SeckillItemRepository;
use ElementVip\Server\Transformers\SeckillItemTransformer;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Redis;
use Carbon\Carbon;

class SeckillController extends Controller
{
    private $seckillRepository;
    private $seckillItemRepository;

    public function __construct(
        SeckillRepository $seckillRepository, SeckillItemRepository $seckillItemRepository

    )
    {
        $this->seckillRepository = $seckillRepository;
        $this->seckillItemRepository = $seckillItemRepository;
    }


    /**获取秒杀活动列表
     * @return \Dingo\Api\Http\Response
     */
    public function lists()
    {

        $limit = !empty(request('limit')) ? request('limit') : 15;

        $lists = $this->seckillItemRepository->getSeckillItemAll($limit);

        $data = $lists->sortBy('starts_at')->values();

        $lists = new LengthAwarePaginator($data, $lists->total(), $limit, $lists->currentPage());

        return $this->response()->paginator($lists, new SeckillItemTransformer());
    }


}