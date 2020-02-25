<?php

namespace GuoJiangClub\Catering\Server\Http\Controllers;

use GuoJiangClub\Catering\Component\Point\Repository\PointRepository;
use GuoJiangClub\Catering\Component\User\Repository\UserRepository;
use GuoJiangClub\Catering\Server\Transformers\PointTransformer;
use Dingo\Api\Http\Response;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    private $user;
    private $point;

    public function __construct(
        UserRepository $userRepository,
        PointRepository $pointRepository)
    {
        $this->user  = $userRepository;
        $this->point = $pointRepository;
    }

    public function myPoint(Request $request)
    {
        return $this->userPoint($request->user()->id, $request['type']);
    }

    public function myPointLogs(Request $request)
    {
        /*if ($request['type'] === null){
            return $this->pointLogs(Authorizer::getResourceOwnerId());
        }
        return $this->pointLogsByType(Authorizer::getResourceOwnerId(), $request['type']);*/
    }

    public function pointLogs($id)
    {
        /*$logs = $this->point->findByField('user_id',$id);

        return $this->response()->collection($logs, new PointTransformer())->setMeta($meta);*/
    }

    public function userPoint($id, $type = null)
    {
        $point          = $this->point->getSumPoint($id, $type);
        $pointValid     = $this->point->getSumPointValid($id, $type);
        $pointFrozen    = $this->point->getSumPointFrozen($id, $type);
        $pointOverValid = $this->point->getSumPointOverValid($id, $type);

        $data = [
            'point'          => $point,
            'pointValid'     => $pointValid,
            'pointFrozen'    => $pointFrozen,
            'pointOverValid' => $pointOverValid,
        ];

        return new Response($data);
    }

    public function pointLogsByType($id, $type)
    {
        $logs = $this->point->findWhere([
            'user_id' => $id,
            'type'    => $type,
        ]);

        /* //获取VipeakAPI数据
         $mobile = $this->user->find($id)->mobile;
         $vipeakLogs = $this->vipeak->getIntegral($mobile);
         $meta['vipeakPoints'] = $vipeakLogs;*/

        return $this->response()->item($logs, new PointTransformer())->setMeta($meta);
    }

    public function addPointLog(Request $request)
    {
        $log = $this->point->create($request->all());

        return $this->response()->item($log, new PointTransformer());
    }

    public function delPointLog($id)
    {
        $log = $this->point->findByField('user_id', $id)->delete();

        return $this->response()->item($log, new PointTransformer());
    }
}