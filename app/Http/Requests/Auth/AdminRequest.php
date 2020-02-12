<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\Request;

class AdminRequest extends Request
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'login' => [
                'account' => 'required|between:4,40|string',
                'password' => ['required', 'string', 'min:6']
            ],
            'update' => [
                'name' => 'between:3,20|regex:/^[a-zA-Z]([-_a-zA-Z0-9]{3,20})+$/', // 通过路由参数 user 隐式获取的用户对象实例
                'email'=>'email',
                'phone'=>[
                    'regex:/^((13[0-9])|(14[5,7])|(15[0-3,5-9])|(17[0,3,5-8])|(18[0-9])|166|198|199)\d{8}$/',
                ],
                'current_password' => 'required_with:new_password|string|min:6',
                'new_password' => 'required_with:current_password|string|min:6|confirmed',  // new_password_confirmation
            ],
        ];
        return $this->useRule($rules);
    }

    public function attributes()
    {
        return [
            'password' => '密码',
            'name' => '用户名',
            'email' => '邮箱',
            'phone' => '手机号',
            'current_password' => '当前密码',
            'new_password' => '新密码'
        ];
    }

    public function messages()
    {
        $messages = [
            'account.required' => '账号不能为空',
            'account.between' => '账号必须介于 4 - 40 个字符之间',
            'new_password.confirmed' => '请输入确认密码或确认密码和密码不一致',
            'new_password.min' => '密码长度不低于 6 个字符',
            'name.regex' => '用户名首字母必须为字母，且只能为字母、数字、下划线、横杠、介于 3 - 20 个字符之间',
            'phone.regex' => '手机号格式不正确',
            'current_password.required_with' => '当前密码不能为空',
            'new_password.required_with' => '新密码不能为空',
            'email.email' => '邮箱格式错误'
        ];

        $messages = array_merge(parent::messages(), $messages);

        return $messages;
    }
}
