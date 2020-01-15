<?php

namespace GuoJiangClub\EC\Catering\Backend\Http\Controllers\PointMall;

use GuoJiangClub\EC\Catering\Backend\Http\Controllers\Controller;
use GuoJiangClub\Catering\Component\Shipping\Models\Shipping;
use GuoJiangClub\EC\Catering\Backend\Models\ShippingMethod;
use GuoJiangClub\EC\Catering\Backend\Models\Order;
use GuoJiangClub\EC\Catering\Backend\Repositories\OrderRepository;
use GuoJiangClub\EC\Catering\Backend\Repositories\OrderItemRepository;
use GuoJiangClub\EC\Catering\Backend\Facades\OrderService;
use Encore\Admin\Facades\Admin as LaravelAdmin;
use Encore\Admin\Layout\Content;
use Illuminate\Http\Request;
use Response;
use Excel;
use DB;

class OrdersController extends Controller
{
	protected $orderRepository;
	protected $orderItemsRepository;
	protected $productRepository;
	protected $cache;

	public function __construct(OrderRepository $orderRepository
		, OrderItemRepository $orderItemsRepository
	)
	{
		$this->orderRepository      = $orderRepository;
		$this->orderItemsRepository = $orderItemsRepository;
		$this->cache                = cache();
	}

	public function index()
	{
		$view = request('status');

		$condition = $this->createConditions();
		$where     = $condition[0];
		$time      = $condition[1];
		$more      = $condition[2];
		$pay_time  = $condition[3];

		$more_filter = request('more_filter');

		$freightCompany = ShippingMethod::all();

		$orders = $this->orderRepository->getExportOrdersData($where, 50, $time, $more, $pay_time);

		return LaravelAdmin::content(function (Content $content) use ($orders, $view, $freightCompany, $more_filter) {

			$content->header('积分订单列表');

			$content->breadcrumb(
				['text' => '积分订单管理', 'url' => 'store/point-mall/orders?status=all', 'no-pjax' => 1],
				['text' => '积分订单列表', 'url' => '', 'no-pjax' => 1, 'left-menu-active' => '积分订单管理']

			);

			$content->body(view('catering-backend::point_mall.orders.index', compact('orders', 'view', 'freightCompany', 'more_filter')));
		});
	}

	protected function createConditions()
	{
		$time     = [];
		$where    = [];
		$more     = [];
		$pay_time = [];

		$where['type'] = ['=', 5];

		if (request('status') == 'all' OR !request('status')) {
			$where['status'] = ['>', 0];
		} else {
			$where['status'] = request('status');
		}

		if (!empty($pay_status = request('pay_status'))) {
			$where['pay_status'] = $pay_status == 'paid' ? 1 : 0;
		}

		if (!empty(request('order_status'))) {
			$where['status'] = request('order_status');
		}

		if (!empty(request('user_id'))) {
			$where['user_id'] = request('user_id');
		}

		if (!empty(request('value')) AND !empty(request('field'))) {
			$where[request('field')] = ['like', '%' . request('value') . '%'];
		}

		if (!empty(request('etime')) && !empty(request('stime'))) {
			$where['created_at'] = ['<=', request('etime')];
			$time['created_at']  = ['>=', request('stime')];
		}

		if (!empty(request('etime'))) {
			$time['created_at'] = ['<=', request('etime')];
		}

		if (!empty(request('stime'))) {
			$time['created_at'] = ['>=', request('stime')];
		}

		/*付款时间*/
		if (!empty(request('s_pay_time')) && !empty(request('e_pay_time'))) {
			$where['pay_time']    = ['<=', request('e_pay_time')];
			$pay_time['pay_time'] = ['>=', request('s_pay_time')];
		}

		if (!empty(request('e_pay_time'))) {
			$pay_time['pay_time'] = ['<=', request('e_pay_time')];
		}

		if (!empty(request('s_pay_time'))) {
			$pay_time['pay_time'] = ['>=', request('s_pay_time')];
		}

		/*more*/
		if (request('more_filter')) {
			/*支付方式*/
			if (!empty(request('pay_method'))) {
				$more['pay_method'] = request('pay_method');
			}

			/*物流*/
			if (!empty(request('distribution'))) {
				$where['distribution'] = request('distribution');
			}

			/*订单金额*/
			if (!empty(request('stotal')) && !empty(request('etotal'))) {
				$more['total'] = [request('stotal') * 100, request('etotal') * 100];
			} elseif (!empty(request('stotal'))) {
				$more['total'] = [request('stotal') * 100, 99999999];
			} elseif (!empty(request('etotal'))) {
				$more['total'] = [0, request('etotal') * 100];
			}

			/*收货地区*/
			if (!empty(request('province')) && !empty(request('city'))) {
				$more['address_name'] = ['like', '%' . request('province') . ' ' . request('city') . '%'];
			} elseif (!empty(request('province'))) {
				$more['address_name'] = ['like', '%' . request('province') . '%'];
			}
		}

		return [$where, $time, $more, $pay_time];
	}

	/**
	 * Display the specified resource.
	 *
	 * @param int $id
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function show($id)
	{
		//
		$order             = $this->orderRepository->findOrThrowException($id);
		$order_deliver     = $order->deliver;
		$order->pay_type   = isset($order->payment->channel) ? $order->payment->channel : '';
		$adjustments       = $this->orderRepository->getOrderAdjustments($order);
		$shipping          = $this->orderRepository->getShippingMethod($order);
		$orderPoint        = $this->orderRepository->getPointMallOrderPoints($id);
		$orderConsumePoint = $this->orderRepository->getOrderConsumePoint($order->id);

		$supplierIds = [];
		if (request('supplier')) {
			$supplierIds = [request('supplier')];
		}
		$isSupplier = session('admin_check_supplier');

		return LaravelAdmin::content(function (Content $content) use ($order, $order_deliver, $shipping, $adjustments, $orderPoint, $orderConsumePoint, $supplierIds, $isSupplier) {

			$content->header('积分订单详情');

			$content->breadcrumb(
				['text' => '积分订单管理', 'url' => 'store/point-mall/orders?status=all', 'no-pjax' => 1],
				['text' => '积分订单详情', 'url' => '', 'no-pjax' => 1, 'left-menu-active' => '积分订单管理']

			);

			$content->body(view('catering-backend::point_mall.orders.show', compact('order', 'order_deliver', 'orderGoods', 'shipping', 'adjustments', 'orderPoint', 'orderConsumePoint', 'supplierIds', 'isSupplier')));
		});
//        return view('catering-backend::point_mall.orders.show', compact('order', 'order_deliver', 'orderGoods', 'shipping', 'adjustments', 'orderPoint', 'orderConsumePoint', 'supplierIds', 'isSupplier'));
	}

	/**
	 * @param Request $request
	 * @param         $id
	 *
	 * @return mixed
	 */

	public function ordersdeliver(Request $request, $id)
	{
		$order_id       = $id;
		$freightCompany = ShippingMethod::all();
		$redirect_url   = request('redirect_url');

		return view('catering-backend::orders.includes.order_deliver', compact('order_id', 'freightCompany', 'redirect_url'));
	}

	/**
	 * @param Request $request
	 * @param         $id
	 *
	 * @return mixed
	 */

	public function ordersdeliveredit(Request $request, $id)
	{
		$freightCompany = ShippingMethod::all();
		$shipping       = Shipping::where(['order_id' => $id])->first();
		$order_id       = $id;

		return view('catering-backend::orders.includes.order_deliver_edit', compact('order_id', 'freightCompany', 'shipping'));
	}

	/**
	 * 后台发货动作
	 *
	 * @param Request $request
	 * @param         $id
	 */
	public function deliver(Request $request)
	{
		$data = $request->except('shipping_id');

		if (!$data['method_id'] OR !$data['tracking']) {
			return $this->ajaxJson(false, [], 403, '请完善发货信息');
		}

		if (!$order = Order::find($data['order_id'])) {
			return $this->ajaxJson(false, [], 403, '订单不存在');
		}

		if (request('shipping_id')) {
			if (Shipping::where(['id' => request('shipping_id')])->update($data)) {
				return $this->ajaxJson();
			}

			return $this->ajaxJson(false, [], 403, '未成功修改发货记录');
		} else {
			if ($shipping = Shipping::create($data)) {
				$order->status              = Order::STATUS_DELIVERED;
				$order->send_time           = $shipping->delivery_time;
				$order->distribution_status = 1;
				$order->distribution        = $shipping->method_id;
				$order->save();

				return $this->ajaxJson();
			}

			return $this->ajaxJson(false, [], 403, '未成功创建发货记录');
		}
	}

	/**
	 * 批量修改订单信息
	 *
	 * @return mixed
	 */
	public function ordersimport()
	{
		return view('catering-backend::orders.includes.order_import');
	}

	public function importorder(Request $request)
	{
		$filename   = 'public' . $request['upload_excel'];
		$error_list = [];
		try {
			DB::beginTransaction();
			Excel::load($filename, function ($reader) use (&$error_list) {
				$reader = $reader->getSheet(0);
				//获取表中的数据
				$results = $reader->toArray();
				foreach ($results as $key => $value) {
					if ($key != 0) {
						$order          = $this->orderRepository->findWhere(['order_no' => $value[0]])->first();
						$freightCompany = ShippingMethod::where(['name' => trim($value[2])])->first();
						//如果该订单号存在则进行订单数据覆盖修改操作(快递号，订单状态修改为已发货状态)
						if (count($order) && $order->pay_status == 1 && count($freightCompany)) {
							//更新订单表
							$orders = [
								'status'              => 3,
								'distribution_status' => 1,
								'distribution'        => $freightCompany->id,
								'send_time'           => date('y-m-d H:i:s', time()),
							];
							$this->orderRepository->update($orders, $order->id);
							//添加快递信息
							$order_deliver = [
								'order_id'      => $order->id,
								'method_id'     => $freightCompany->id,
								'tracking'      => $value[1],
								'delivery_time' => date('y-m-d H:i:s', time()),
							];
							Shipping::create($order_deliver);
						} else {
							$error_list[] = $value[0];
						}
					}
				}
			});
			DB::commit();

			return response()->json(['status'       => true
			                         , 'error_code' => 0
			                         , 'data'       => ['error_list' => $error_list],
			]);
		} catch (\Exception $exception) {
			DB::rollBack();
			\Log::info($exception);

			return $this->ajaxJson(false, [], 404, '操作失败');
		}
	}

	public function exportJob()
	{
		$page  = request('page') ? request('page') : 1;
		$limit = request('limit') ? request('limit') : 50;

		$view = request('status');

		if ($view == 'all') {
			$where['status'] = ['>', 0];
		} else {
			$where['status'] = $view;
		}

		$time = [];

		if (!empty(request('value'))) {
			$where[request('field')] = ['like', '%' . request('value') . '%'];
		}

		if (!empty(request('etime')) && !empty(request('stime'))) {
			$where['created_at'] = ['<=', request('etime')];
			$time['created_at']  = ['>=', request('stime')];
		}

		if (!empty(request('etime'))) {
			$time['created_at'] = ['<=', request('etime')];
		}

		if (!empty(request('stime'))) {
			$time['created_at'] = ['>=', request('stime')];
		}

		$orders   = $this->orderRepository->getExportOrders($where, $limit, $time);
		$lastPate = $orders->lastPage();

		/*if (count($orders) == 0) {
			//没有数据后直接下载文件
			return Response::download(storage_path('exports') . '/' . session('export_order_data'));
		}*/

		foreach ($orders as $item) {
			$item->pay_type   = isset($item->payment->channel) ? $item->payment->channel : '';
			$item->channel_no = isset($item->payment->channel_no) ? $item->payment->channel_no : '';
		}

		$title = ['订单编号', '所属品牌', '订单类型', '下单会员', '收货人', '省', '市', '区', '收货地址', '联系电话', '邮箱地址', '商品名称', 'sku', '吊牌价', '售价', '购买数量', '商品应付金额', '优惠活动名称', '优惠抵扣金额', '积分抵扣金额', '订单状态', '支付状态', '支付平台', '支付流水号', '发货状态', '订单应付金额', '下单时间', '付款时间'];

		$excelName = 'order-data-' . date('Y_m_d_H_i_s', time());

		$orderExcelData = OrderService::formatToExcelData($orders);

		if ($this->cache->has('export_orders_cache') AND $page !== 1) {
			$cacheData = $this->cache->get('export_orders_cache');
			$this->cache->put('export_orders_cache', array_merge($cacheData, $orderExcelData), 30);
		} else {
			$this->cache->put('export_orders_cache', $orderExcelData, 30);
		}

		if ($page == $lastPate) {

			$ordersData = $this->cache->get('export_orders_cache');
			$excel      = Excel::create($excelName, function ($excel) use ($ordersData, $title) {
				$excel->sheet('orders', function ($sheet) use ($ordersData, $title) {
					$sheet->prependRow(1, $title);
					$sheet->rows($ordersData);
				});
			})->store(request('type'), storage_path('exports'), false);
			$this->cache->forget('export_orders_cache');

			return Response::download(storage_path('exports') . '/' . $excelName . '.' . request('type'));
		} else {
			$message  = '正在导出订单数据';
			$interval = 3;
			$url_bit  = route('admin.orders.export.job', array_merge(['page' => $page + 1, 'limit' => $limit], request()->except('page', 'limit')));

			return view('catering-backend::show_message', compact('message', 'url_bit', 'interval'));
		}
	}

	/**
	 * 获取需要导出的数据
	 */
	public function getExportData()
	{
		$page  = request('page') ? request('page') : 1;
		$limit = request('limit') ? request('limit') : 50;
		$type  = request('type');

		$condition = $this->createConditions();
		$where     = $condition[0];
		$time      = $condition[1];
		$more      = $condition[2];
		$pay_time  = $condition[3];

		$orders = $this->orderRepository->getExportOrdersData($where, 50, $time, $more, $pay_time);

		$lastPage = $orders->lastPage();

		foreach ($orders as $item) {
			$item->pay_type   = isset($item->payment->channel) ? $item->payment->channel : '';
			$item->channel_no = isset($item->payment->channel_no) ? $item->payment->channel_no : '';
		}

		$orderExcelData = OrderService::formatToExcelData($orders, '');

		if ($page == 1) {
			session(['export_orders_cache' => generate_export_cache_name('export_orders_cache_')]);
		}
		$cacheName = session('export_orders_cache');

		if ($this->cache->has($cacheName)) {
			$cacheData = $this->cache->get($cacheName);
			$this->cache->put($cacheName, array_merge($cacheData, $orderExcelData), 300);
		} else {
			$this->cache->put($cacheName, $orderExcelData, 300);
		}

		if ($page == $lastPage) {
			$title = ['订单编号', '所属品牌', '供应商', '订单类型', '下单会员', '收货人', '省', '市', '区', '收货地址', '联系电话', '邮箱地址', '商品名称', 'sku', '规格', '吊牌价', '售价', '购买数量', '商品应付金额', '优惠活动名称', '优惠抵扣金额', '积分抵扣金额', '订单状态', '支付状态', '支付平台', '支付流水号', '发货状态', '订单应付金额', '下单时间', '付款时间', '售后状态'];

			return $this->ajaxJson(true, ['status' => 'done', 'url' => '', 'type' => $type, 'title' => $title, 'cache' => $cacheName, 'prefix' => 'orders_data_']);
		} else {
			$url_bit = route('admin.orders.getExportData', array_merge(['page' => $page + 1, 'limit' => $limit], request()->except('page', 'limit')));

			return $this->ajaxJson(true, ['status' => 'goon', 'url' => $url_bit, 'page' => $page, 'totalPage' => $lastPage]);
		}
	}

	/**
	 * 修改收货地址
	 */
	public function editAddress($order_id)
	{
		$order   = Order::find($order_id);
		$address = explode(' ', $order->address_name);

		return view('catering-backend::orders.includes.modal_address', compact('order', 'address'));
	}

	public function postAddress()
	{
		$order               = Order::find(request('order_id'));
		$order->accept_name  = request('accept_name');
		$order->mobile       = request('mobile');
		$order->address      = request('address');
		$order->address_name = request('province') . ' ' . request('city') . ' ' . request('district');
		$order->save();

		return $this->ajaxJson();
	}
}