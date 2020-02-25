<?php

namespace ElementVip\Server\Http\Controllers;

use GuoJiangClub\Catering\Component\Order\Models\Order;
use GuoJiangClub\Catering\Component\Order\Models\OrderItem;
use ElementVip\Server\Transformers\OrdersTransformer;
use Illuminate\Http\Request;
use ElementVip\Server\Repositories\OrderRepository;
use ElementVip\Component\Product\Models\Goods;
use Anam\PhantomMagick\Converter;
use Carbon\Carbon;
use ElementVip\Server\Services\MiniProgramService;


class OrderController extends Controller
{

    protected $orderRepository;

    protected $goodsRepository;

    protected $converter;

    protected $miniProgramService;

    public function __construct(

        OrderRepository $orderRepository, Converter $converter, MiniProgramService $miniProgramService

    )
    {
        $this->orderRepository = $orderRepository;

        $this->converter = $converter;

        $this->miniProgramService = $miniProgramService;
    }

    public function getOrders()
    {
        $orderConditions['channel'] = \request('channel') ? \request('channel') : 'ec';

        if (request('order_no')) {
            $orderConditions['order_no'] = request('order_no');
        }

        if (request('status')) {
            $orderConditions['status'] = request('status');
        } else {
            $orderConditions ['status'] = ['status', '<>', 0];
            $orderConditions ['status2'] = ['status', '<>', 9];
        }

        $offline = request('offline') ? request('offline') : 0;

        if (\request('channel') == 'shop') {
            $type = request('type') ? request('type') : [6];
        } else {
            $type = request('type') ? request('type') : [0, 1, 4, 7, 8, 9, 10];
        }

        $orderConditions ['user_id'] = request()->user()->id;

        $itemConditions = [];

        if (request('goods_name')) {
            $itemConditions['item_name'] = ['item_name', 'like', '%' . request('goods_name') . '%'];
        }

        if (request('goods_id')) {
            $itemConditions['item_id'] = request('goods_id');
        }

        $limit = request('limit') ? request('limit') : 10;

        if ($criteria = request('criteria')) {
            $itemConditions['order_no'] = ['order_no', 'like', '%' . $criteria . '%'];
            $itemConditions['item_name'] = ['item_name', 'like', '%' . $criteria . '%'];
            $itemConditions['item_id'] = ['item_id', 'like', '%' . $criteria . '%'];

            $order = $this->orderRepository->getOrdersByCriteria($orderConditions, $itemConditions, $limit, $offline, $type);

        } else {
            $order = $this->orderRepository->getOrdersByConditions($orderConditions, $itemConditions,
                $limit, ['items', 'shippings', 'refunds', 'payments', 'items.refunds', 'adjustments', 'items.product', 'items.product.goods'], $offline, $type);
        }

        $transformer = request('transformer') ? request('transformer') : 'list';
        return $this->response()->paginator($order, new OrdersTransformer($transformer));

    }

    public function getOrderDetailsOffline($orderno)
    {
        $details = $this->orderRepository->getOrderOffilineByNo($orderno);
        if ($details) {
            return $this->api($details->toArray());
        }
        return $this->api('', false);
    }

    public function getOrderDetails($orderno)
    {
        $details = $this->orderRepository->getOrderByNo($orderno);
        return $this->response()->item($details, new OrdersTransformer());
    }

    public function getRefundItems($order_no)
    {
        if (!$order_no || !$order = $this->orderRepository->getOrderByNo($order_no)) {
            return $this->response()->errorBadRequest('订单不存在');
        }

        $refunds = $order->refunds()->get();

        if ($refunds->count() == 0) {
            return $this->api(0);
        }

        $orderItems = OrderItem::whereIn('id', $refunds->pluck('order_item_id')->toArray())->get();

        return $this->api($orderItems);
    }

    public function getOrdersByStatus()
    {
        $status = request('status');
        $orders = $this->orderRepository->scopeQuery(function ($query) use ($status) {
            $query = $query->where('channel', 'ec')->where('user_id', request()->user()->id);
            if (is_array($status)) {
                $query = $query->whereIn('status', $status);
            } else {
                $query = $query->where('status', $status);
            }
            return $query;
        });
        if ($orders) {
            return $this->api($orders->all());
        } else {
            return $this->api('', false);
        }
    }

    /**
     * 获取能够进行售后维修的订单
     */
    public function getRefundOrders()
    {
        $orderConditions = [];
        /*if (request('order_no')) {
            $orderConditions['order_no'] = request('order_no');
        }

        if (request('status')) {
            $orderConditions['status'] = request('status');
        } else {
            $orderConditions ['status'] = ['status', '<>', 0];
            $orderConditions ['status2'] = ['status', '<>', 9];
        }*/

        $orderConditions ['channel'] = 'ec';
        $orderConditions ['status'] = ['status', '<>', 0];
        $orderConditions ['status2'] = ['status', '<>', 9];
        $orderConditions ['status3'] = ['status', '<>', 1];
        $orderConditions ['status4'] = ['status', '<>', 8];
        $orderConditions ['status5'] = ['status', '<>', 6];
        $orderConditions ['status6'] = ['status', '<>', 5];

        $offline = request('offline') ? request('offline') : 0;
        $type = request('type') ? request('type') : [0, 1];

        $orderConditions ['user_id'] = request()->user()->id;

        $itemConditions = [];

        $limit = request('limit') ? request('limit') : 15;

        if ($criteria = request('criteria')) {
            $itemConditions['order_no'] = ['order_no', 'like', '%' . $criteria . '%'];
            $itemConditions['item_name'] = ['item_name', 'like', '%' . $criteria . '%'];
            $itemConditions['item_id'] = ['item_id', 'like', '%' . $criteria . '%'];
        }

        $order = $this->orderRepository->getOrdersByCriteria($orderConditions, $itemConditions, $limit, $offline, $type);

        return $this->response()->paginator($order, new OrdersTransformer('refund'));
    }

    public function getPointOrders()
    {
        $orderConditions = [];

        if (request('status')) {
            $orderConditions['status'] = request('status');
        } else {
            $orderConditions ['status'] = ['status', '<>', 0];
            $orderConditions ['status2'] = ['status', '<>', 9];
        }

        $offline = request('offline') ? request('offline') : 0;

        $type = request('type') ? request('type') : [5];

        $orderConditions ['user_id'] = request()->user()->id;

        $itemConditions = [];

        $limit = request('limit') ? request('limit') : 15;

        $order = $this->orderRepository->getOrdersByConditions($orderConditions, $itemConditions, $limit, ['items'], $offline, $type);

        return $this->response()->paginator($order, new OrdersTransformer());
    }

    /**
     * @param $orderno
     * @return \Dingo\Api\Http\Response|void
     */
    public function shareOrder($orderno)
    {

        $user = request()->user();

        $limit = request('limit') ? request('limit') : 10;

        $status = [0, 1, 6, 8, 9];

        $goods_id_arr = [];

        $order = $this->orderRepository->getShareOrderByNo($orderno);

        if (!$orderno || !$order || in_array($order['status'], $status)) {

            return $this->response()->errorBadRequest('订单不存在');
        }

        $avatar = $this->orderRepository->getPurchasedUsersAvatar($order['goods_id_arr'], $status);

        if (count($avatar) > 0) {

            $avatar = array_values(array_unique($avatar));

            if (count($avatar) > $limit) {

                $avatar = array_slice($avatar, 0, $limit);

            }

        }


        $order['users_avatar'] = $avatar;

        $goods = Goods::where('is_commend', 1)->where('is_del', 0)->inRandomOrder()->take(6)->get(['id', 'name', 'img', 'sell_price']);

        $order['commend_goods'] = $goods;

        $order['is_share_order'] = 0;
        if (request()->isMethod('post') AND $user AND $user->id == $order['user_id']) {
            $order['is_share_order'] = 1;
        }

        return $this->success($order);

    }


    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getShareOrderView($order_no)
    {
        $status = [0, 1, 6, 8, 9];

        $order = $this->orderRepository->getShareOrderByNo($order_no);

        if (!$order_no || !$order || in_array($order['status'], $status)) {

            return $this->response()->errorBadRequest('订单不存在');
        }

        return view('server::share.order', compact('order', 'order_no'));
    }


    /**
     * @return \Dingo\Api\Http\Response
     */
    public function getShareOrderImg()
    {


        $order_no = request('order_no') ? request('order_no') : 0;

        $pages = request('pages') ? request('pages') : '';


        $status = [0, 1, 6, 8, 9];

        $order = $this->orderRepository->getShareOrderByNo($order_no);

        if (!$order_no || !$order || in_array($order['status'], $status)) {

            return $this->response()->errorBadRequest('订单不存在');
        }

        $name = date('Y-m-d', strtotime($order['submit_time'])) . '_' . $order_no . '_share_order' . '.png';

        $dimension = request('dimension') ? request('dimension') : '575px';

        $zoomfactor = request('zoomfactor') ? request('zoomfactor') : 1.5;

        $quality = request('quality') ? request('quality') : 100;

        $options = [
            'dimension' => $dimension,
            'zoomfactor' => $zoomfactor,
            'quality' => $quality
        ];

        //获取小程序码
        $mini_code = $this->miniProgramService->createMiniQrcode($pages, 800, $order_no, 'share_order');

        if (!$mini_code) {

            return $this->failed('生成小程序码失败');
        }

        $path = storage_path('app/public/images/share_order/');

        $file = $path . $name;

        $url = asset('storage/images/share_order/' . $name);

        if (file_exists($file)) {

            return $this->success(['url' => $url]);
        }

        $route = url('api/order/share/view', $order_no);

        $this->converter->source($route)->toPng($options)->save($file);

        $this->image_png_size_add($file, $path . $name);

        return $this->success(['url' => $url]);

    }


    protected function image_png_size_add($imgsrc, $imgdst)
    {
        list($width, $height, $type) = getimagesize($imgsrc);
        $new_width = $width * 1;
        $new_height = $height * 1;
        header('Content-Type:image/png');
        $image_wp = imagecreatetruecolor($new_width, $new_height);
        $image = imagecreatefrompng($imgsrc);
        imagecopyresampled($image_wp, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
        imagejpeg($image_wp, $imgdst, 100);
        imagedestroy($image_wp);

    }

}