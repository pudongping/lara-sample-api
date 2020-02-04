<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\Request;

class UserRequest extends Request
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'register' => [
                'account' => 'required|between:4,40',
                'password' => ['required', 'string', 'min:8', 'confirmed'],  // password_confirmation
            ],
        ];

        return $this->useRule($rules);
    }

    public function attributes()
    {
        return [
            'password' => '密码',
        ];
    }

    public function messages()
    {
        $messages = [
            'account.required'      => '账号不能为空',
            'account.between'       => '账号必须介于 4 - 40 个字符之间',
            'password.confirmed'    => '请输入确认密码或确认密码和密码不一致',
        ];

        $messages = array_merge(parent::messages(), $messages);

        return $messages;
    }

}
