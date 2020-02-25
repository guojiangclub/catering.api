<?php

namespace GuoJiangClub\EC\Catering\Server\Http\Controllers;

use ElementVip\Component\Suit\Repositories\SuitRepository;
use ElementVip\Component\Suit\Repositories\SuitItemRepository;
use ElementVip\Component\Suit\Services\SuitService;
use ElementVip\Component\Product\Repositories\GoodsRepository;
use ElementVip\Component\Product\Models\Specification;

use GuoJiangClub\Catering\Component\Order\Models\Order;
use GuoJiangClub\Catering\Component\Order\Models\OrderItem;
use ElementVip\Component\Address\Models\Address;
use ElementVip\Component\Order\Processor\OrderProcessor;
use ElementVip\Component\Point\Repository\PointRepository;
use ElementVip\Server\Transformers\SuitTransformer;

class SuitController extends Controller
{
	private $SuitRepository;

	private $SuitItemRepository;

	private $SuitService;

	private $GoodsRepository;

	private $orderProcessor;

	private $pointRepository;

	public function __construct(
		SuitRepository $SuitRepository
		, SuitItemRepository $SuitItemRepository
		, SuitService $SuitService
		, GoodsRepository $GoodsRepository
		, OrderProcessor $orderProcessor
		, PointRepository $pointRepository
	)
	{
		$this->SuitRepository     = $SuitRepository;
		$this->SuitItemRepository = $SuitItemRepository;
		$this->SuitService        = $SuitService;
		$this->GoodsRepository    = $GoodsRepository;
		$this->orderProcessor     = $orderProcessor;
		$this->pointRepository    = $pointRepository;
	}

	/**获取套餐列表
	 *
	 * @param $id
	 *
	 * @return \Dingo\Api\Http\Response
	 */
	public function index($id)
	{
		$user  = request()->user();
		$suits = $this->SuitRepository->getSuitById($id);
		if (!count($suits)) {
			return $this->api([], false, 400, '套餐不存在');
		};

		foreach ($suits->items as $suit) {
			if (count($suit->goods->products) > 0) {
				$suit->goods->no_spec = false;
			} else {
				$suit->goods->no_spec = true;
			}
		}

		return $this->api($suits);
	}

	/**获取库存
	 *
	 * @param $id
	 *
	 * @return \Dingo\Api\Http\Response
	 */
	public function getStock($id)
	{
		$qty = '';
		if (!empty(request('sku_id'))) {
			$qty = $this->SuitItemRepository->getSkuQtyByItemId(request('sku_id'));
		}

		$goods    = $this->GoodsRepository->findOneById($id);
		$specs    = [];
		$stores   = [];
		$skuPhoto = collect();

		if ($goods AND count($products = $goods->products)) {
			$grouped = $goods->specificationValue->groupBy('spec_id');

			foreach ($products as $key => $val) {
				$specArray = $val->specID;
				asort($specArray);

				$spec_id                = implode('-', $specArray);
				$stores[$spec_id]['id'] = $val->id;

				if (!$qty || $val->id == intval(request('sku_id'))) {
					$stores[$spec_id]['store'] = $val->is_show == 1 ? $val->store_nums : 0;
				} else {
					$stores[$spec_id]['store'] = 0;
				}

				$stores[$spec_id]['price'] = $val->sell_price;
				$stores[$spec_id]['sku']   = $val->sku;
				$stores[$spec_id]['ids']   = $val->specID;

				//产品图片
				if ($photo = $val->photo) {
					$skuPhotoData['spec_value_id'] = $spec_id;
					$skuPhotoData['photo']         = $photo->url;
					$skuPhoto->push($skuPhotoData);
				}
			}

			foreach ($grouped as $key => $item) {

				$sort = $key == 1 ? $key + 2 : $key;

				$specs[$sort]['id'] = $key;

				$spec = Specification::find($key);

				$specs[$sort]['label'] = $spec->name;
				$specs[$sort]['list']  = [];
				$item                  = $item->sortBy('pivot.sort')->values();
				foreach ($item as $k => $value) {
					$list          = [];
					$list['id']    = $value->id;
					$list['value'] = $value->name;

					if ($value->spec_id == 2)    //颜色
					{
						$list['color'] = '#' . $value->rgb;

						//图片数据,兼容Osprey 从sku获取图片
						$filter = $skuPhoto->filter(function ($item) use ($value) {
							$specIdArr = explode('-', $item['spec_value_id']);

							return in_array($value->id, $specIdArr);
						})->first();

						if ($filter) {
							$list['img'] = $filter['photo'];
						} else {
							$list['img'] = $this->getImageCdnUrl($value->pivot->img);
						}

						$list['spec_img'] = $this->getImageCdnUrl($value->pivot->img);

						$list['alias'] = $value->pivot->alias;
					}
					array_push($specs[$sort]['list'], $list);
				}
			}
		}

		return $this->api([
			'specs'  => $specs,
			'stores' => $stores,
		]);
	}

	public function getSuitList()
	{
		$limit = request('limit') ? request('limit') : 15;

		$list = $this->SuitRepository->suitList($limit);

		return $this->response()->paginator($list, new SuitTransformer());
	}
}