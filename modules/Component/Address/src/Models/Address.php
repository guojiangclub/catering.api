<?php

namespace GuoJiangClub\Catering\Component\Address\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $guarded = ['id'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $prefix = config('ibrand.app.database.prefix', 'ibrand_');

        $this->setTable($prefix . 'address');
    }

    /**
     * 获取当前用户所有收货地址
     *
     * @param $id
     *
     * @return mixed
     */
    public static function getAddressesByUser($user_id)
    {
        return self::where('user_id', '=', $user_id)->orderBy('updated_at', 'desc')->get();
    }

    /**
     * 新增收货地址
     *
     * @param $new_address
     */
    public static function CreateNew($new_address)
    {

        $createNew = self::create($new_address);

        if (!$createNew) {
            return false;
        }

        if ($new_address['is_default'] == 1) {
            self::where(function ($query) use ($new_address, $createNew) {
                $query->where('user_id', '=', $new_address['user_id'])
                    ->where('id', '!=', $createNew['id']);
            })
                ->update(['is_default' => 0]);
        }

        return $createNew;
    }

    /**
     *
     * 修改收货地址
     *
     * @param $update_address
     *
     * @return array
     */
    public static function UpdateAddress($update_address)
    {
        if ($update_address['is_default'] == 1) {
            self::where(['user_id' => $update_address['user_id']])->update(['is_default' => 0]);
        }
        $updateAddress = self::where(['id' => $update_address['id'], 'user_id' => $update_address['user_id']])->update($update_address);
        if (!$updateAddress) {
            return false;
        }

        return $updateAddress;
    }

    /**
     * 删除收货地址
     *
     * @param $id
     * @param $user_id
     *
     * @return mixed
     */
    public static function DeleteAddress($id, $user_id)
    {
        return self::where(['user_id' => $user_id, 'id' => $id])->delete();
    }

    /**
     * 收货地址详细
     *
     * @param $id
     * @param $user_id
     *
     * @return mixed
     */
    public static function getAddressDetails($id, $user_id)
    {
        $details = self::where(['id' => $id, 'user_id' => $user_id])->first();
        if (!$details) {
            return false;
        }

        return $details;
    }

    /**
     * 获取默认收货地址
     *
     * @param $id
     *
     * @return array
     */
    public static function getDefaultAddress($id)
    {
        $allAddress = self::getAddressesByUser($id);

        if (count($allAddress) == 0) {
            return [];
        }

        $defaultAddress = $allAddress->filter(function ($address) {
            return $address->is_default == 1;
        })->first();

        if (!$defaultAddress) {
            $defaultAddress = $allAddress->first();
        }

        return $defaultAddress;
    }

}