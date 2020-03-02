<?php

namespace App\Http\Requests\Admin\Product;

use App\Http\Requests\Request;

class ProductBrandRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        // 编辑时，当前品牌的 id
        $currentBrandId = $this->brand->id ?? 0;

        $rules = [
            'store' => [
                'category_ids' => 'required|array',
                'name' => 'required|string|max:255|min:2|unique:product_brands',
                'description' => 'string|max:255',
                'sort' => 'integer|min:0',
                'status' => 'integer',
            ],
            'update' => [
                'category_ids' => 'required|array',
                'name' => 'required|string|max:255|min:2|unique:product_brands,name,'.$currentBrandId,
                'description' => 'string|max:255',
                'sort' => 'integer|min:0',
                'status' => 'integer',
            ],
        ];

        return $this->useRule($rules);
    }

    public function attributes()
    {
        return [
            'category_ids' => '当前选择的类目',
            'name' => '品牌名称',
            'description' => '品牌描述',
            'sort' => '排序编号',
            'status' => '状态',
        ];
    }

    public function messages()
    {
        $messages = [
            'category_ids.array' => '提交的类目数据格式错误',
        ];

        $messages = array_merge(parent::messages(), $messages);

        return $messages;
    }

}
