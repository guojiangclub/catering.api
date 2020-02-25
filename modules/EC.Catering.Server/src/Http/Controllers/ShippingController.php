<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016-11-17
 * Time: 12:43
 */

namespace ElementVip\Server\Http\Controllers;

use ElementVip\Component\Shipping\Models\ShippingMethod;

class ShippingController extends Controller
{
    public function getMethods()
    {
        return $this->api(ShippingMethod::all());
    }
}