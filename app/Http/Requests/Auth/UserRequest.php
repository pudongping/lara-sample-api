<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\Request;
use App\Models\Auth\User;

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
                'captcha_key' => 'required|string',
                'captcha_code' => 'required|string',
            ],
            'socialStore' => [
                'code' => 'required_without:access_token|string',
                'access_token' => 'required_without:code|string',
            ],
        ];

        // 微信授权登录的流程中换取用户信息的接口，需要同时提交 access_token 和 openid
        if (User::$loginType[User::SOCIAL_WEIXIN] == $this->social_type && !$this->code) {
            $rules['socialStore']['openid']  = 'required|string';
        }

        return $this->useRule($rules);
    }

    public function attributes()
    {
        return [
            'password' => '密码',
            'captcha_key' => '图片验证码的 key',
            'captcha_code' => '图片验证码',
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
