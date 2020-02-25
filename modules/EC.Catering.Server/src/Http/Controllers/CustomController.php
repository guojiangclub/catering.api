<?php
/**
 * Created by PhpStorm.
 * User: eddy
 * Date: 2018/5/25
 * Time: 12:07
 */

namespace ElementVip\Server\Http\Controllers;


use ElementVip\Cms\Models\PageTranslation;

class CustomController extends Controller
{
    public function page($id)
    {
        $page = PageTranslation::where('page_id', $id)->first()->toArray();
        return $this->api($page);
    }
}