<?php

namespace App\Http\Requests\Common;

use App\Http\Requests\Request;

class ImageRequest extends Request
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
                'type' => 'required|string',
            ],
        ];

        if ($this->type == 'avatar') {
            $rules['store']['image'] = 'required|mimes:jpeg,bmp,png,gif|dimensions:min_width=200,min_height=200';
        } else {
            $rules['store']['image'] = 'required|mimes:jpeg,bmp,png,gif';
        }

        return $this->useRule($rules);
    }

    public function attributes()
    {
        return [
            'type' => '图片类型',
            'image' => '图片资源',
        ];
    }

    public function messages()
    {
        $messages = [
            'image.dimensions' => '图片的清晰度不够，宽和高都需要 200px 以上',
        ];

        $messages = array_merge(parent::messages(), $messages);

        return $messages;
    }

}
