<?php

namespace GuoJiangClub\Catering\Server\Http\Controllers;

use GuoJiangClub\Catering\Component\Address\Models\Address;
use Validator;

class AddressController extends Controller
{

    public function __construct()
    {

    }

    /**
     * 获取用户收货地址
     *
     * @return mixed
     */
    public function getAddress()
    {
        $AddressList = Address::getAddressesByUser(request()->user()->id);

        return $this->success($AddressList);
    }

    /**
     * 新建收货地址
     *
     * @return mixed
     */
    public function createNew()
    {
        $input = request()->all();

        $validator = Validator::make($input, [
            'accept_name'  => 'required',
            'mobile'       => 'required',
            'province'     => 'required',
            'city'         => 'required',
            'area'         => 'required',
            'address_name' => 'required',
            'address'      => 'required',
            'is_default'   => 'required',
        ]);

        if ($validator->fails()) {
            return $this->failed($validator->errors()->first());
        }

        $input['user_id'] = request()->user()->id;

        if (!$address = Address::CreateNew($input)) {
            return $this->failed('创建地址失败');
        }

        return $this->success($address);
    }

    /**
     * 修改收货地址
     */
    public function updateAddress()
    {
        $update_address            = request()->except('Authorization');
        $update_address['user_id'] = request()->user()->id;
        if (!$address = Address::UpdateAddress($update_address)) {
            return $this->failed('修改地址失败');
        }

        return $this->success($address);
    }

    /**
     * 删除收货地址
     *
     * @param $id
     *
     * @return mixed
     */
    public function deleteAddress($id)
    {
        $user_id = request()->user()->id;

        if (!Address::DeleteAddress($id, $user_id)) {
            return $this->failed('删除收货地址失败');
        }

        return $this->success();
    }

    /**
     * 收货地址详细
     *
     * @param $id
     *
     * @return mixed
     */
    public function getAddressDetails($id)
    {
        $user_id = request()->user()->id;

        if (!$Addressdetails = Address::getAddressDetails($id, $user_id)) {
            return $this->failed('获取收货地址失败');
        }

        return $this->success($Addressdetails);
    }

    /**
     * 获取默认收货地址
     */
    public function getDefaultAddress()
    {
        $user_id        = request()->user()->id;
        $DefaultAddress = Address::getDefaultAddress($user_id);

        return $this->success($DefaultAddress);
    }

}