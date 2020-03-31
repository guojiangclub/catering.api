<?php

namespace GuoJiangClub\EC\Catering\Backend\Http\Controllers;

use GuoJiangClub\EC\Catering\Backend\Models\AttributeValue;
use GuoJiangClub\EC\Catering\Backend\Models\Goods;
use GuoJiangClub\Catering\Component\Product\Models\GoodsAttr;
use GuoJiangClub\EC\Catering\Backend\Models\Models;
use GuoJiangClub\EC\Catering\Backend\Models\Spec;
use GuoJiangClub\EC\Catering\Backend\Repositories\GoodsRepository;
use Illuminate\Http\Request;
use GuoJiangClub\EC\Catering\Backend\Models\Attribute;
use GuoJiangClub\EC\Catering\Backend\Repositories\ModelsRepository;
use GuoJiangClub\EC\Catering\Backend\Repositories\SpecRepository;
use GuoJiangClub\EC\Catering\Backend\Repositories\AttributeRepository;
use DB;
use Encore\Admin\Facades\Admin as LaravelAdmin;
use Encore\Admin\Layout\Content;

class GoodsModelsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    protected $modelsRepository;
    protected $attributeRepository;
    protected $specRepository;
    protected $goodsRepository;

    public function __construct(ModelsRepository $modelsRepository
        , AttributeRepository $attributeRepository
        , SpecRepository $specRepository
        , GoodsRepository $goodsRepository
    )
    {
        $this->modelsRepository    = $modelsRepository;
        $this->attributeRepository = $attributeRepository;
        $this->specRepository      = $specRepository;
        $this->goodsRepository     = $goodsRepository;
    }

    public function index()
    {
        $models = $this->modelsRepository->all();

        return LaravelAdmin::content(function (Content $content) use ($models) {

            $content->header('模型列表');

            $content->breadcrumb(
                ['text' => '模型管理', 'url' => '', 'no-pjax' => 1],
                ['text' => '模型列表', 'url' => '', 'no-pjax' => 1, 'left-menu-active' => '模型管理']

            );

            $content->body(view('catering-backend::model.index', compact('models')));
        });
    }

    public function create()
    {
        $spec       = Spec::all();
        $attributes = Attribute::where('model_id', 0)->get();

        return LaravelAdmin::content(function (Content $content) use ($spec, $attributes) {

            $content->header('新增商品模型');

            $content->breadcrumb(
                ['text' => '模型管理', 'url' => 'store/models', 'no-pjax' => 1],
                ['text' => '新增商品模型', 'url' => '', 'no-pjax' => 1, 'left-menu-active' => '模型管理']

            );

            $content->body(view('catering-backend::model.create', compact('spec', 'attributes')));
        });
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input      = $request->except('_token');
        $specIds    = isset($input['spec_ids']) ? $input['spec_ids'] : [];
        $attrIds    = isset($input['attr_ids']) ? $input['attr_ids'] : [];
        $updateAttr = isset($input['_attr']) ? $input['_attr'] : [];
        $attribute  = isset($input['attr']) ? $input['attr'] : [];

        $base = ['name' => $input['name'], 'spec_ids' => $specIds];
        if (!$specIds) {
            unset($base['spec_ids']);
        }

        try {
            DB::beginTransaction();

            if (request('id')) {
                $model = Models::find(request('id'));
                $model->fill($base);
                $model->save();

                foreach ($updateAttr as $key => $item) {
                    $updated           = Attribute::find($item['id']);
                    $item['is_search'] = isset($item['is_search']) ? $item['is_search'] : 0;
                    $item['is_chart']  = isset($item['is_chart']) ? $item['is_chart'] : 0;
                    if (isset($attribute[$key]))  //create new attribute value
                    {
                        $updated->fill(array_merge_recursive($item, $attribute[$key]))->save();

                        $updated->values()->createMany($this->getAttrValue2($attribute[$key]));
                        unset($attribute[$key]);
                    } else {
                        $updated->fill($item)->save(); //update attribute
                    }

                    if (isset($item['value']) AND count($item['value'])) //update attribute value
                    {
                        foreach ($input['_attr_value_id'][$key] as $k => $val) {
                            AttributeValue::find($val)->fill(['name' => $item['value'][$k]])->save();
                        }
                    }
                }
            } else { //create
                $model = Models::create($base);
            }

            //create attribute AND attribute_value
            $attr = $model->attribute()->createMany($attribute);
            foreach ($attr as $item) {
                if ($item->value) {
                    $name = $this->getAttrValue($item->value);
                    $item->values()->createMany($name);
                }
            }

            /*sync model_attribute relation*/
            $model->hasManyAttribute()->sync($attrIds);

            DB::commit();

            return response()->json(['status'       => true
                                     , 'error_code' => 0
                                     , 'error'      => ''
                                     , 'data'       => '']);
        } catch (\Exception $exception) {
            DB::rollBack();

            \Log::info($exception->getMessage() . $exception->getTraceAsString());

            return response()->json(['status'       => false
                                     , 'error_code' => 404
                                     , 'error'      => '保存失败'
                                     , 'data'       => '']);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $model      = Models::find($id);
        $spec       = Spec::all();
        $attributes = Attribute::where('model_id', 0)->get();

        $attrIds = [];
        foreach ($model->hasManyAttribute as $attribute) {
            array_push($attrIds, $attribute->pivot->attribute_id);
        }
        $goodsCount = Goods::where('model_id', $id)->count();

        return LaravelAdmin::content(function (Content $content) use ($model, $spec, $attributes, $attrIds, $goodsCount) {

            $content->header('编辑商品模型');

            $content->breadcrumb(
                ['text' => '模型管理', 'url' => 'store/models', 'no-pjax' => 1],
                ['text' => '编辑商品模型', 'url' => '', 'no-pjax' => 1, 'left-menu-active' => '模型管理']

            );

            $content->body(view('catering-backend::model.edit', compact('model', 'spec', 'attributes', 'attrIds', 'goodsCount')));
        });
//        return view('catering-backend::model.edit', compact('model', 'spec', 'attributes', 'attrIds', 'goodsCount'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        $count = $this->goodsRepository->findByField('model_id', $id)->count();
        if (!$count) {
            $model = Models::find($id);

            foreach ($model->attribute as $item) {
                $item->values()->delete();
            }
            $model->attribute()->delete();
            $model->delete();

            return $this->ajaxJson(true);
        } else {
            return $this->ajaxJson(false);
        }
    }

    protected function getAttrValue($value)
    {
        $arr  = explode(',', $value);
        $name = [];
        foreach ($arr as $val) {
            $name[]['name'] = $val;
        }

        return $name;
    }

    protected function getAttrValue2($value)
    {
        $name = [];
        foreach ($value['value'] as $val) {
            $name[]['name'] = $val;
        }

        return $name;
    }

    /**
     * 删除属性值
     *
     * @param $id
     *
     * @return mixed
     */
    public function deleteAttrValue($id)
    {
        $result = GoodsAttr::where('attribute_value_id', $id)->first();
        if ($result) {
            return $this->ajaxJson(false);
        } else {
            AttributeValue::find($id)->delete();

            return $this->ajaxJson(true);
        }
    }

    /**
     * 删除属性
     *
     * @param $id
     */
    public function deleteAttr($id)
    {
        $result = GoodsAttr::where('attribute_id', $id)->first();
        if ($result) {
            return $this->ajaxJson(false);
        } else {
            Attribute::find($id)->delete();

            return $this->ajaxJson(true);
        }
    }

    /**
     * 关联规格状态变化检测
     *
     * @param $id
     */
    public function checkSpec($id, $model_id)
    {
        $result = DB::table(config('ibrand.app.database.prefix', 'ibrand_') . 'goods')
            ->join(config('ibrand.app.database.prefix', 'ibrand_').'goods_spec_relation', config('ibrand.app.database.prefix', 'ibrand_').'goods.id', '=', 'el_goods_spec_relation.goods_id')
            ->where([config('ibrand.app.database.prefix', 'ibrand_').'goods.model_id' => $model_id, config('ibrand.app.database.prefix', 'ibrand_').'goods_spec_relation.spec_id' => $id])
            ->get();

        if (count($result)) {
            return $this->ajaxJson(false);
        }

        return $this->ajaxJson(true);
    }

}
