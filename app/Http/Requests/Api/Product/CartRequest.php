<?php

namespace App\Http\Requests\Api\Product;

use App\Http\Requests\Request;
use App\Models\Product\ProductSku;
use App\Models\Product\ProductSpu;

class CartRequest extends Request
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
                'sku_id' => [
                    'required',
                    function ($attribute, $value, $fail) {
                        if (!$sku = ProductSku::find($value)) {
                            return $fail('该商品不存在');
                        }
                        if (!in_array($sku->spu->original_status, [ProductSpu::STATUS_PUTWAY])) {
                            return $fail('该商品未上架');
                        }
                        if (0 === $sku->stock) {
                            return $fail('该商品已售罄');
                        }
                        if ($this->input('amount') > 0 && ($sku->stock < $this->input('amount'))) {
                            return $fail('该商品库存不足');
                        }
                    }
                ],
                'amount' => ['required', 'integer', 'min:1']
            ],
        ];

        return $this->useRule($rules);
    }

    public function attributes()
    {
        return [
            'amount' => '商品数量'
        ];
    }

    public function messages()
    {
        $messages = [
            'sku_id.required' => '没有选择商品'
        ];

        $messages = array_merge(parent::messages(), $messages);

        return $messages;
    }

}
