<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\Request;

class VerificationCodeRequest extends Request
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
                'phone' => [
                    'required',
                    'regex:/^((13[0-9])|(14[5,7])|(15[0-3,5-9])|(17[0,3,5-8])|(18[0-9])|166|198|199)\d{8}$/',
                    'unique:users'
                ]
            ],
        ];

        return $this->useRule($rules);
    }

    public function messages()
    {
        $messages = [
            'phone.required' => '手机号不能为空',
            'phone.regex'    => '手机号格式错误',
            'phone.unique'   => '手机号已被占用，请重新填写',
        ];

        $messages = array_merge(parent::messages(), $messages);

        return $messages;
    }
}
