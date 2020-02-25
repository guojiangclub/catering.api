<?php
/**
 * Created by PhpStorm.
 * User: eddy
 * Date: 2018/7/25
 * Time: 17:18
 */

namespace ElementVip\Server\Http\Controllers;


use Carbon\Carbon;
use ElementVip\Component\User\Models\User;
use ElementVip\Server\Transformers\MultiGrouponItemTransformer;
use ElementVip\Server\Transformers\MultiGrouponTransformer;
use iBrand\Component\MultiGroupon\Models\MultiGrouponItems;
use iBrand\Component\MultiGroupon\Models\MultiGrouponUsers;
use iBrand\Component\MultiGroupon\Repositories\MultiGrouponItemRepository;
use iBrand\Component\MultiGroupon\Repositories\MultiGrouponRepository;
use iBrand\Component\MultiGroupon\Service\MultiGrouponService;
use iBrand\FreeEvent\Core\Services\FreeService;
use Storage;

class MultGrouponController extends Controller
{
    protected $multiGrouponService;
    protected $multiGrouponItemRepository;
    protected $freeService;
    protected $multiGrouponRepository;

    public function __construct(MultiGrouponService $multiGrouponService,
                                MultiGrouponItemRepository $multiGrouponItemRepository,
                                FreeService $freeService,
                                MultiGrouponRepository $multiGrouponRepository)
    {
        $this->multiGrouponService = $multiGrouponService;
        $this->multiGrouponItemRepository = $multiGrouponItemRepository;
        $this->freeService = $freeService;
        $this->multiGrouponRepository = $multiGrouponRepository;
    }

    public function getGrouponUserList()
    {
        $user = auth('api')->user();
        $userList = $this->multiGrouponService->getGrouponUserList($user, request('goods_id'), request('multi_groupon_item_id'));
        return $this->api($userList);
    }

    public function getGrouponItems()
    {
        $user = auth('api')->user();
        $items = $this->multiGrouponItemRepository->getGrouponItemList($user, request('multi_groupon_id'), request('multi_groupon_item_id'));
        return $this->response()->paginator($items, new MultiGrouponItemTransformer());
    }

    /**
     * 拼团详情
     */
    public function showItem()
    {
        $item = $this->multiGrouponItemRepository->getGrouponItemByID(request('multi_groupon_item_id'));

        return $this->response()->item($item, new MultiGrouponItemTransformer('show'))->setMeta(['server_time' => Carbon::now()->toDateTimeString()]);
    }

    /**
     * 小程序分享图片
     * @return \Dingo\Api\Http\Response
     */
    public function createShareImage()
    {
        $itemID = request('multi_groupon_item_id');
        $goodsID = request('goods_id');
        $user = request()->user();
        $grouponUser = MultiGrouponUsers::where('user_id', $user->id)->where('multi_groupon_items_id', $itemID)->first();
        $grouponID = $grouponUser->multi_groupon_id;
        $imgUrl = env('APP_URL') . '/storage/multi-groupon/' . $grouponID . '/user_' . $user->id . '.jpg';

        if ($grouponUser->share_img) {
            return $this->api(['image' => $grouponUser->share_img]);
        }

        //1、获取用户头像
        $avatar = $this->freeService->createUserAvatar($user, 80);

        //2、小程序码      
        $mini_qrcode = $this->multiGrouponService->createMiniQrcode('pages/store/collage/collage', 420, $itemID, $grouponID, $user);
        if (!$mini_qrcode) {
            return $this->api(null, false, 404, '生成二维码失败，请重试');
        }

        //3、填充海报二维码 头像等信息
        $tempImgPath = storage_path('app/public/multi-groupon/' . $grouponID . '/user_' . $user->id . '.jpg');
        $this->multiGrouponService->insertText($mini_qrcode, $avatar, $tempImgPath, $grouponID, $user);

        $grouponUser->share_img = $imgUrl;
        $grouponUser->save();

        return $this->api(['image' => $imgUrl]);
    }

    public function grouponList()
    {
        $limit = request('limit') ? request('limit') : 10;
        $list = $this->multiGrouponRepository->getGrouponList($limit);

        return $this->response()->paginator($list, new MultiGrouponTransformer());
    }

}