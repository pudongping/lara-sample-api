<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\Request;
use App\Models\Auth\User;
use Illuminate\Validation\Rule;

class UserRequest extends Request
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $userId = auth('api')->id();

        $rules = [
            'register' => [
                'account' => 'required|between:4,40',
                'password' => ['required', 'string', 'min:6', 'confirmed'],  // password_confirmation
                'captcha_key' => 'required|string',
                'captcha_code' => 'required|string',
            ],
            'socialStore' => [
                'code' => 'required_without:access_token|string',
                'access_token' => 'required_without:code|string',
            ],
            'store' => [
                'account' => 'required|between:4,40|string',
                'password' => ['required', 'string', 'min:6']
            ],
            'update' => [
                'name' => 'between:3,20|regex:/^[a-zA-Z]([-_a-zA-Z0-9]{3,20})+$/|unique:users,name,' .$userId,
                'email'=>'email|unique:users,email,'.$userId,
                'phone'=>[
                    'regex:/^((13[0-9])|(14[5,7])|(15[0-3,5-9])|(17[0,3,5-8])|(18[0-9])|166|198|199)\d{8}$/',
                    Rule::unique('users')->ignore($userId),
                ],
                // images 表中 id 是否存在，type 是否为 avatar，守卫名称是否为 api，用户 id 是否是当前登录的用户 id
                // 'avatar_image_id' => 'exists:images,id,type,avatar,user_id,'.$userId,
                'avatar_image_id' => [
                    Rule::exists('images', 'id')->where(function ($query) use ($userId) {
                        $query->where('type', 'avatar')->where('guard_name', 'api')->where('user_id', $userId);
                    }),
                ],
                'current_password' => 'required_with:new_password|string|min:6',
                'new_password' => 'required_with:current_password|string|min:6|confirmed',  // new_password_confirmation
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
            'name' => '用户名',
            'email' => '邮箱',
            'phone' => '手机号',
            'avatar_image_id' => '用户头像图片资源 id',
            'current_password' => '当前密码',
            'new_password' => '新密码'
        ];
    }

    public function messages()
    {
        $messages = [
            'account.required'      => '账号不能为空',
            'account.between'       => '账号必须介于 4 - 40 个字符之间',
            'password.confirmed'    => '请输入确认密码或确认密码和密码不一致',
            'password.min'          => '密码长度不低于 6 个字符',
            'name.regex'            => '用户名首字母必须为字母，且只能为字母、数字、下划线、横杠、介于 3 - 20 个字符之间',
            'phone.regex'           => '手机号格式不正确',
            'current_password.required_with' => '当前密码不能为空',
            'new_password.required_with' => '新密码不能为空',
            'email.email' => '邮箱格式错误',
            'new_password.confirmed' => '请输入确认密码或确认密码和密码不一致',
            'new_password.min' => '密码长度不低于 6 个字符',
        ];

        $messages = array_merge(parent::messages(), $messages);

        return $messages;
    }

}
