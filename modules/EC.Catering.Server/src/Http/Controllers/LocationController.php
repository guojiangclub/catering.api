<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016-12-01
 * Time: 20:15
 */

namespace ElementVip\Server\Http\Controllers;


use DB;
use ElementVip\Server\Models\City;
use ElementVip\Server\Models\Shop;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function index()
    {

    }

    public function shop($id = null)
    {

        $shops = Shop::City($id)->where('is_enable',1)->get();

        return $this->api($shops->toArray(),true, 0, "");
    }

    public function city()
    {
        $cities = City::AllCity()->groupBy('letter');

        return $this->api($cities,true, 0, "");
    }

    public function search(Request $request)
    {
        $where = [];
//return 1;
        $query = app(Shop::class);

        if ($city = request('city') AND $city != 0) {
//            $where['city'] = $city;
            $query = $query->where('city', '=', $city);
        }

        if ($keyword = request('keyword')) {
            $where['name'] = ['name', 'like', '%' . $keyword . '%'];
            $where['address'] = ['address', 'like', '%' . $keyword . '%'];
        }

        $query->where(function ($query) use($where) {
            foreach ($where as $field => $value) {
                if (is_array($value)) {
                    list($field, $condition, $val) = $value;
                    $query = $query->orWhere($field, $condition, $val);
                } else {
                    $query = $query->where($field, '=', $value);
                }
            }
        });


        $shops = $query->where('is_enable',1)->orderBy('top', 'desc')->with('tag')->get();

        return $this->api($shops,true, 0, "");
        //return view('welcome');
    }

    public function hottest(Request $request)
    {
        $shops = DB::table('storelocator_search')->where('city', $request->input('city'))->get();
        return $this->api($shops,true, 0, "");
        //return view('welcome');
    }
}