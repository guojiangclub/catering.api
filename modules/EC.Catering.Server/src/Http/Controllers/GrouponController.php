<?php

namespace ElementVip\Server\Http\Controllers;

use iBrand\Component\Groupon\Repositories\GrouponRepository;
use iBrand\Component\Groupon\Repositories\GrouponItemRepository;
use ElementVip\Server\Transformers\GrouponItemTransformer;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;
use ElementVip\Component\Order\Repositories\OrderRepository;


class GrouponController extends Controller
{
    private $grouponRepository;
    private $grouponItemRepository;
    protected $orderRepository;

    public function __construct(
        GrouponRepository $grouponRepository, GrouponItemRepository $grouponItemRepository
        ,OrderRepository $orderRepository

    )
    {
        $this->grouponRepository= $grouponRepository;
        $this->grouponItemRepository = $grouponItemRepository;
        $this->orderRepository=$orderRepository;
    }


    /**获取拼团活动列表
     * @return \Dingo\Api\Http\Response
     */
    public function index()
    {
        $limit = !empty(request('limit')) ? request('limit') : 15;

        $lists = $this->grouponItemRepository->findActive($limit);

        $data = $lists->sortBy('starts_at')->values();

        $lists = new LengthAwarePaginator($data, $lists->total(), $limit, $lists->currentPage());

        return $this->response()->paginator($lists, new GrouponItemTransformer());
    }


}