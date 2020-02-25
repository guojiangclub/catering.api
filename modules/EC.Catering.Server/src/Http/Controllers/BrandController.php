<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/10/29
 * Time: 17:35
 */

namespace ElementVip\Server\Http\Controllers;

use ElementVip\Component\Brand\Models\Brand;

class BrandController extends Controller
{
    public function index()
    {
        $brands = Brand::All();
        return $this->api($brands);
    }

    public function show($id)
    {
        $brand = Brand::find($id);
        return $this->api($brand);
    }
}