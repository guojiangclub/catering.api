<?php

namespace GuoJiangClub\Catering\Backend\Http\Controllers;

use Carbon\Carbon;
use iBrand\Backend\Http\Controllers\Controller;
use GuoJiangClub\Catering\Component\Shipping\Models\Shipping;
use GuoJiangClub\EC\Catering\Backend\Models\OrderItem;
use GuoJiangClub\EC\Catering\Backend\Model\OrderProduce;
use GuoJiangClub\Catering\Component\Shipping\Models\ShippingMethod;
use GuoJiangClub\EC\Catering\Backend\Models\Order;
use Illuminate\Http\Request;
use Response;
use GuoJiangClub\Catering\Backend\Repositories\OrderRepository;
use Excel;
use GuoJiangClub\EC\Catering\Backend\Facades\OrderService;
use DB;
use Encore\Admin\Facades\Admin as LaravelAdmin;
use Encore\Admin\Layout\Content;

class OrdersController extends Controller
{
	protected $orderRepository;
	protected $productRepository;
	protected $orderLogRepository;
	protected $cache;

	public function __construct(OrderRepository $orderRepository)
	{
		$this->orderRepository = $orderRepository;
		$this->cache           = cache();
	}

	public function index()
	{
		$view = request('status')?request('status'):'all';
		
		$users     = null;
		$condition = $this->createConditions();
		$where     = $condition[0];
		$time      = $condition[1];
		$more      = $condition[2];
		$pay_time  = $condition[3];

		$orders = $this->orderRepository->getExportOrdersData($where, 20, $time, $more, $pay_time);

		return LaravelAdmin::content(function (Content $content) use ($orders, $view, $users) {

			$content->header('订单列表');

			$content->breadcrumb(
				['text' => '订单列表', 'no-pjax' => 1, 'left-menu-active' => '订单管理']
			);

			$content->body(view('backend-shitang::orders.index', compact('orders', 'view', 'users')));
		});
	}

	protected function createConditions()
	{
		$time     = [];
		$where    = [];
		$more     = [];
		$pay_time = [];

		$where['channel'] = 'st';
		if (request('status') == 'all' OR !request('status')) {
			$where['status'] = ['>', 0];
		} else {
			$where['status'] = request('status');
		}

		if (request('type') == 'wx_pub') {
			$where['type'] = 0;
		}

		if (request('type') && request('type') != 'wx_pub') {
			$where['type'] = request('type');
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

		return [$where, $time, $more, $pay_time];
	}

	public function show($id)
	{

		$order         = $this->orderRepository->findOrThrowException($id);
		$order_deliver = $order->deliver;
		// $order->pay_type = isset($order->payment->channel) ? $order->payment->channel : '';
		$adjustments       = $this->orderRepository->getOrderAdjustments($order);
		$shipping          = $this->orderRepository->getShippingMethod($order);
		$orderPoint        = $this->orderRepository->getOrderPoints($order->items);
		$orderConsumePoint = $this->orderRepository->getOrderConsumePoint($order->id);

		$supplierIds = [];
		if (request('supplier')) {
			$supplierIds = [request('supplier')];
		}

		$isSupplier = session('admin_check_supplier');
		/*兼容以前订单发货数据显示*/
		$prevShipping = false;
		$items        = $order->items->filter(function ($value) {
			return $value->shipping_id == 0;
		});
		if ($order->distribution_status == 1 AND count($order->items) == count($items)) {
			$prevShipping = true;
		}

		return LaravelAdmin::content(function (Content $content) use ($order, $order_deliver, $shipping, $adjustments, $orderPoint, $orderConsumePoint, $prevShipping, $isSupplier, $supplierIds) {

			$content->header('订单详情');

			$content->breadcrumb(
				['text' => '订单列表', 'no-pjax' => 1, 'left-menu-active' => '订单管理']
			);

			$content->body(view('backend-shitang::orders.show', compact('order', 'order_deliver', 'orderGoods', 'shipping', 'adjustments', 'orderPoint', 'orderConsumePoint', 'prevShipping', 'isSupplier', 'supplierIds')));
		});
	}

	/**
	 * @param Request $request
	 * @param array   $con
	 */

	public function orderslist(Request $request, $con = [])
	{

		$con    = $request->except('_token');
		$orders = $this->orderRepository->orderSearch($con);
		$view   = request('status');

		return view('backend.orders.includes.orders_list', compact('orders', 'view'));
	}

	/**
	 * 单个订单发货modal
	 *
	 * @param  $id
	 *
	 * @return mixed
	 */

	public function ordersDeliver($id)
	{
		$order_id          = $id;
		$freightCompany    = ShippingMethod::all();
		$redirect_url      = request('redirect_url');
		$order             = Order::find($id);
		$is_deliver_enable = OrderService::checkOrderDeliver($order);
		$status            = OrderService::checkOrderRefund($order);

		return view('backend-shitang::orders.includes.order_deliver', compact('order_id', 'freightCompany', 'redirect_url', 'status', 'is_deliver_enable'));
	}

	/**
	 * 合并发货modal
	 *
	 * @param $user_id
	 *
	 * @return mixed
	 */

	public function ordersMultipleDeliver()
	{
		$freightCompany = ShippingMethod::all();
		$redirect_url   = request('redirect_url');

		$orders        = Order::whereIn('id', request('ids'))->get();
		$countMulOrder = count($orders);
		$status        = true;
		$order_id      = implode(',', request('ids'));

		/*判断合并的订单是否包含当前登录的供应商*/
		$is_deliver_enable = false;
		$disableCount      = 0;
		foreach ($orders as $order) {
			if (!$deliver_enable = OrderService::checkOrderDeliver($order)) {
				$disableCount++;
			}
		}
		if ($disableCount < $countMulOrder) {
			$is_deliver_enable = true;
		}

		/*判断是否有进行中的售后订单*/
		$refundOrder = $orders->filter(function ($value, $key) {
			return !OrderService::checkOrderRefund($value);
		});
		if (count($refundOrder)) {
			$status = false;
		}

		return view('backend-shitang::orders.includes.order_multiple_deliver', compact('order_id', 'freightCompany', 'redirect_url', 'status', 'is_deliver_enable'));
	}

	/**
	 * @param Request $request
	 * @param         $id
	 *
	 * @return mixed
	 */

	public function ordersDeliverEdit(Request $request, $id)
	{
		$freightCompany = ShippingMethod::all();
		$orderItem      = $this->orderItemsRepository->findWhere(['order_id' => $id, 'supplier_id' => session('admin_supplier_id')[0]])->first();
		if ($orderItem) {
			$shipping = Shipping::find($orderItem->shipping_id);
		} else {
			$shipping = Shipping::where(['order_id' => $id])->first();
		}
		$order_id = $id;

		return view('backend-shitang::orders.includes.order_deliver_edit', compact('order_id', 'freightCompany', 'shipping'));
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

		if (!isset($data['method_id']) OR !$data['method_id'] OR !$data['tracking']) {
			return $this->ajaxJson(false, [], 403, '请完善发货信息');
		}

		$orderIds = explode(',', $data['order_id']);
		if (empty($orderIds)) {
			return $this->ajaxJson(false, [], 403, '订单不存在');
		}

		try {
			DB::beginTransaction();
			if (request('shipping_id')) {
				foreach ($orderIds as $orderId) {
					$data['order_id'] = $orderId;
					Shipping::where(['id' => request('shipping_id')])->update($data);
				}
			} else {
				$this->delivering($orderIds, $data);
			}
			DB::commit();

			return $this->ajaxJson();
		} catch (\Exception $exception) {
			DB::rollBack();
			\Log::info($exception->getMessage());

			return $this->ajaxJson(false, [], 404, '保存失败');
		}
	}

	/**
	 * 发货操作
	 *
	 * @param $orderIds array 订单id数组
	 *
	 * @return bool
	 */
	public function delivering($orderIds, $shippingMessage)
	{
		foreach ($orderIds as $orderId) {
			$order = Order::find($orderId);
			if (!$order) {
				continue;
			}

			if (!$order->groupon_status) {
				continue;
			}

			$totalItem       = count($order->items);
			$deliveringItems = $order->items->filter(function ($value) { //未发货的item
				return $value->is_send == 0 AND $value->status == 1;
			});
			if (count($deliveringItems) <= 0) {
				continue;
			}

			$hasDelivered    = $totalItem - count($deliveringItems); //已发货的item数量
			$deliveringItems = $deliveringItems->groupBy('supplier_id');
			foreach ($deliveringItems as $key => $item) {
				if (!in_array($key, session('admin_supplier_id'))) {
					continue;
				}

				$shippingMessage['order_id'] = $orderId;
				$shipping                    = Shipping::create($shippingMessage);
				$hasDelivered                += count($item);
				OrderItem::whereIn('id', array_column($item->toArray(), 'id'))->update(['is_send' => 1, 'shipping_id' => $shipping->id]);
				event('order.goods.deliver', [$shipping]);
			}

			if ($hasDelivered > 0 && $hasDelivered < $totalItem) { //如果已发货的item小于所有item数量（即部分发货）
				$order->distribution_status = 2;
			}

			if ($totalItem == $hasDelivered) {
				$order->distribution_status = 1;
				$order->status              = Order::STATUS_DELIVERED;
				$order->send_time           = $shippingMessage['delivery_time'];
				$order->distribution        = $shipping->id;
			}

			$order->save();
		}

		return true;
	}

	// 保存excel到服务器
	public function excelExport()
	{
		$date  = !empty(request()->input('date')) ? request()->input('date') : [];
		$title = !empty(request()->input('title')) ? request()->input('title') : [];
//        return ExcelExportsService::createExcelExport('Orders_', $date, $title);
		if (count($date)) {
			$order_no = [];
			foreach ($date as $val) {
				$order_no[] = $val[0];
			}
			$date = $this->orderRepository->with('items', 'payment')->findWhereIn('order_no', $order_no);
			foreach ($date as $item) {
				$item->pay_type   = isset($item->payment->channel) ? $item->payment->channel : '';
				$item->channel_no = isset($item->payment->channel_no) ? $item->payment->channel_no : '';
			}
		}

		return OrderService::searchAllOrdersExcel('Orders_', $date, $title);
	}

	// 导出下载excel文件
	public function download($url)
	{
		return Response::download(storage_path() . "/exports/$url");
	}

	/**
	 * 批量导入订单发货modal
	 *
	 * @return mixed
	 */
	public function ordersImport()
	{
		return view('backend-shitang::orders.includes.order_import');
	}

	/**
	 * 批量导入订单发货动作
	 *
	 * @param Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function importOrderSend(Request $request)
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
					if ($key <= 0) {
						continue;
					}

					$order_no  = trim($value[0]);
					$ship_name = trim($value[2]);
					$number    = trim($value[1]);
					if (!$order_no OR !$ship_name OR !$number) {
						$error_list[] = '遇到空白行数据，中断导入';
						break;
					}

					$order          = $this->orderRepository->findWhere(['order_no' => $order_no])->first();
					$freightCompany = ShippingMethod::where(['name' => $ship_name])->first();
					if (!$order) {
						$error_list[] = '订单 ' . $order_no . ' 不存在；';
						continue;
					}

					if (!$freightCompany) {
						$error_list[] = '订单 ' . $order_no . ' 对应的快递公司‘' . $ship_name . '’不存在；';
						continue;
					}

					$status = OrderService::checkOrderRefund($order);
					if (!$status) {
						$error_list[] = '订单 ' . $order_no . ' 有未完成的售后；';
						continue;
					}

					$shippingMessage = [
						'method_id'     => $freightCompany->id,
						'tracking'      => $number,
						'delivery_time' => date('y-m-d H:i:s', time()),
					];
					$this->delivering([$order->id], $shippingMessage);
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

	public function orderProduce($id)
	{

		$order = Order::find($id);

		$status = settings()->getSetting('produce_status_setting');

		return view('backend-shitang::orders.includes.order_produce_edit', compact('order', 'status'));
	}

	public function orderProduceUpdate(Request $request)
	{
		$input = $request->except('_token');

		if ($produce = OrderProduce::create($input)) {

			$order                 = Order::find($input['order_id']);
			$order->produce_status = $produce->status;
			$order->save();

			return $this->ajaxJson();
		}

		return $this->ajaxJson(false, [], 403, '修改商品生产状态失败');
	}

	public function close($id)
	{
		$order = Order::find($id);

		$order->status          = Order::STATUS_CANCEL;
		$order->completion_time = Carbon::now();
		$order->save();

		//TODO: 用户未付款前取消订单后，需要还原库存

		return $this->ajaxJson(true, [], 200, '订单取消成功');
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

			return view('backend-shitang::show_message', compact('message', 'url_bit', 'interval'));
		}
		/*if ($page == 1) { //如果是首页，则需要创建excel
			$excel = Excel::create($excelName, function ($excel) use ($orderExcelData, $title) {
				$excel->sheet('orders', function ($sheet) use ($orderExcelData, $title) {
					$sheet->prependRow(1, $title);
					$sheet->rows($orderExcelData);
				});
			})->store('xls', storage_path('exports'), false);

			session(['export_order_data' => $excelName = $excelName . '.xls']);

		} else {

			Excel::load(storage_path('exports') . '/' . session('export_order_data'), function ($reader) use ($orderExcelData) {
				$reader->sheet('orders', function ($sheet) use ($orderExcelData) {
					$sheet->rows($orderExcelData);
				});
			})->store('xls', storage_path('exports'), false);
		}

		$message = '正在导出订单数据';
		$interval = 3;
		$url_bit = route('admin.orders.export.job', array_merge(['page' => $page + 1, 'limit' => $limit], request()->except('page', 'limit')));

		return view('backend-shitang::show_message', compact('message', 'url_bit', 'interval'));*/
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

		$orders   = $this->orderRepository->getExportOrdersData($where, 50, $time, $more, $pay_time);
		$lastPage = $orders->lastPage();

		foreach ($orders as $item) {
			$item->pay_type   = isset($item->payment->channel) ? $item->payment->channel : '';
			$item->channel_no = isset($item->payment->channel_no) ? $item->payment->channel_no : '';
		}

		$orderExcelData = $this->orderRepository->formatToExcelData($orders);

		if ($page == 1) {
			session(['shitang_export_orders_cache' => generate_export_cache_name('shitang_export_orders_cache_')]);
		}
		$cacheName = session('shitang_export_orders_cache');

		if ($this->cache->has($cacheName)) {
			$cacheData = $this->cache->get($cacheName);
			$this->cache->put($cacheName, array_merge($cacheData, $orderExcelData), 300);
		} else {
			$this->cache->put($cacheName, $orderExcelData, 300);
		}

		if ($page == $lastPage) {
			$title = ['订单编号', '支付方式', '下单会员', '订单状态', '总金额', '优惠金额', '余额抵扣', '实付金额', '下单时间', '付款时间', '用户留言'];

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

		return view('backend-shitang::orders.includes.modal_address', compact('order', 'address'));
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
