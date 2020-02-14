<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\Request;
use Illuminate\Validation\Rule;

class RoleRequest extends Request
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'update' => [
                'name'         => [
                    'required',
                    'between:3,25',
                    'regex:/^[0-9A-Za-z\-\_]+$/',
                    Rule::unique('roles')->ignore($this->role),
                ],
                'cn_name'      => 'required|between:3,25',
                'permissions'  => 'array',
            ],
            'store' => [
                'name'         => 'required|between:3,25|regex:/^[0-9A-Za-z\-\_]+$/|unique:roles',
                'cn_name'      => 'required|between:3,25',
                'permissions'  => 'array',
            ],
            'massDestroy' => [
                'ids'         => 'required|array',
            ],
        ];

        return $this->useRule($rules);
    }

    public function messages()
    {
        $messages = [
            'name.unique'          => '角色标识已被占用，请重新填写。',
            'name.regex'           => '角色标识只支持数字、英文、横杠和下划线。',
            'name.between'         => '角色标识必须介于 3 - 25 个字符之间。',
            'name.required'        => '角色标识不能为空。',
            'cn_name.required'     => '角色中文名称不能为空。',
            'cn_name.between'      => '角色中文名称必须介于 3 - 25 个字符之间。',
            'permissions.array'    => '权限数据提交的格式错误。',
            'ids.required'         => '批量删除时，缺乏必要参数',
            'ids.array'            => '批量删除时，数据提交的格式错误。',
        ];

        $messages = array_merge(parent::messages(), $messages);

        return $messages;
    }

}
