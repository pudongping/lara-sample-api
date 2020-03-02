<?php

namespace App\Http\Requests\Admin\Product;

use App\Http\Requests\Request;
use Illuminate\Validation\Rule;
use App\Models\Product\ProductCategory;

class ProductCategoryRequest extends Request
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'store' => [
                'pid' => 'required|integer|min:0|exists:product_categories,id',
                'name' => 'required|string|max:255|min:2',
                'description' => 'string|max:255',
                'sort' => 'integer|min:0',
                'status' => [
                    'integer',
                    Rule::in(array_keys(ProductCategory::$statusMsg))
                ]
            ],
            'update' => [
                'pid' => 'required|integer|min:0|exists:product_categories,id',
                'name' => 'required|string|max:255|min:2',
                'description' => 'string|max:255',
                'sort' => 'integer|min:0',
                'status' => [
                    'integer',
                    Rule::in(array_keys(ProductCategory::$statusMsg))
                ],
            ],
        ];

        return $this->useRule($rules);
    }

    public function attributes()
    {
        return [
            'pid' => '父级类目',
            'name' => '类目名称',
            'description' => '类目描述',
            'sort' => '排序编号',
            'status' => '状态'
        ];
    }

    public function messages()
    {
        $messages = [
            'pid.min' => '父级类目编号不能小于 0',
            'pid.exists' => '当前所选父级类目不存在',
            'status.in' => '类目状态输入错误'
        ];

        $messages = array_merge(parent::messages(), $messages);

        return $messages;
    }

}
