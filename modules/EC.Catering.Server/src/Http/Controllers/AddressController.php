<?php

namespace GuoJiangClub\EC\Catering\Server\Http\Controllers;

use ElementVip\Component\Address\Models\Address;
use Illuminate\Http\Request;
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

        return $this->api($AddressList);
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
            return $this->response()->errorForbidden($validator->errors());
        }

        $input['user_id'] = request()->user()->id;

        if (!$address = Address::CreateNew($input)) {
            return $this->response()->errorBadRequest('创建地址失败');
        }

        return $this->api($address);
    }

    /**
     * 修改收货地址
     *
     * @return array
     */
    public function updateAddress()
    {
        $update_address            = request()->except('Authorization');
        $update_address['user_id'] = request()->user()->id;
        if (!$address = Address::UpdateAddress($update_address)) {
            return $this->response()->errorBadRequest('修改地址失败');
        }

        return $this->api($address);
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
            return $this->response()->errorBadRequest('删除收货地址失败');
        }

        return $this->api();
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
            return $this->response()->errorBadRequest('获取收货地址失败');
        }

        return $this->api($Addressdetails);
    }

    /**
     * 获取默认收货地址
     */
    public function getDefaultAddress()
    {
        $user_id        = request()->user()->id;
        $DefaultAddress = Address::getDefaultAddress($user_id);

        return $this->api($DefaultAddress);
    }

}