<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016-11-10
 * Time: 19:17
 */

namespace ElementVip\Server\Http\Controllers;


use ElementVip\Component\BankAccount\Model\BankInfo;
use ElementVip\Server\Transformers\BankAccountTransformer;

class BankController extends Controller
{
    public function index()
    {
        return $this->response()->collection(BankInfo::all(), new BankAccountTransformer());
    }
}