<?php

namespace ElementVip\Server\Http\Controllers;

use ElementVip\Component\Invoice\Model\InvoiceOrder;
use ElementVip\Component\Invoice\Model\InvoiceUser;
use ElementVip\Component\Invoice\Repository\InvoiceRepository;
use Illuminate\Http\Request;
use ElementVip\Server\Transformers\InvoiceTransformer;
use Validator;

class InvoiceController extends Controller
{
    /**
     * InvoiceController constructor.
     */
    protected $invoice;

    public function __construct(InvoiceRepository $invoice)
    {
        $this->invoice = $invoice;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function invoiceUserCreate()
    {
        $user = request()->user();

        $input=request()->all();

        if (request()->has('type') && request()->get('type') == '电子发票'&&empty(request('app_type'))) {
            $validator = Validator::make(request()->all(), [
                'type' => 'required | in:' . implode(",", config('Invoice.type')),
                'title' => 'required',
                'content' => 'required | in:' . implode(',', config('Invoice.content')),
                'consignee_phone' => 'required',
                'consignee_email' => 'required | email',
            ]);

            if ($validator->fails()) {
                return $this->response()->errorBadRequest($validator->errors());
            };
            if (!isMobile(request()->consignee_phone)) {
                return $this->response()->errorBadRequest('手机号码格式不对');
            }

        }elseif (!empty(request('app_type'))&&request('app_type')=='wechat_app') {

            $validator = Validator::make(request()->all(), [
                'title' => 'required',
            ]);
            if ($validator->fails()) {
                return $this->response()->errorBadRequest($validator->errors());
            };
            request()->offsetSet('type', '普通发票');
            request()->offsetSet('content', '');
            $input=request()->except('app_type');

        }else {
            $validator = Validator::make(request()->all(), [
                'type' => 'required | in:' . implode(",", config('Invoice.type')),
                'title' => 'required',
                'content' => 'required | in:' . implode(',', config('Invoice.content')),
            ]);

            if ($validator->fails()) {
                return $this->response()->errorBadRequest($validator->errors());
            };
        }


        $invoice = InvoiceUser::create(array_merge(['user_id' => $user->id], $input));

        return $this->api($invoice);
    }

    public function invoiceOrderCreate()
    {

        $validator = Validator::make(request()->all(), [
            'type' => 'required | in:' . implode(",", config('Invoice.type')),
            'order_id' => 'required',
            'title' => 'required',
            'content' => 'required | in:' . implode(',', config('Invoice.content')),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors(),
            ]);
        };

        InvoiceOrder::create(request()->all());
        return response()->json([
            'success' => true,
            'message' => 'success',
        ]);
    }

    public function invoiceOrderUpdate($id)
    {

        $validator = Validator::make(request()->all(), [
            'type' => 'required | in:' . implode(",", config('Invoice.type')),
            'title' => 'required',
            'content' => 'required | in:' . implode(',', config('Invoice.content')),

        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors(),
            ]);
        };

        InvoiceOrder::findOrFail($id)->update(request()->all());
        return response()->json([
            'success' => true,
            'message' => 'success',
        ]);
    }

    public function invoiceOrderAdminEdit($id)
    {

        $validator = Validator::make(request()->all(), [
            'number' => 'required',
            'invoice_at' => 'required | date'
        ]);


        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors(),
            ]);
        };

        InvoiceOrder::findOrFail($id)->update(array_merge(['admin_id' => request()->user()->id], request()->only(['number', 'invoice_at'])));
        return response()->json([
            'success' => true,
            'message' => 'success',
        ]);
    }


    public function getInvoiceUser()
    {
        $user = request()->user();

        return $this->response()->collection($this->invoice->userInvoice($user->id)->orderBy('updated_at', 'desc')->get()
            , new InvoiceTransformer());

    }

    public function getInvoiceConfig()
    {
        if (settings('invoice_status')) {
           return  $this->api(['type' => config('Invoice.type'),
                'content' => config('Invoice.content'),]);
        }

        return $this->api('',false);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
