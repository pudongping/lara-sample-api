<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\Request;
use Illuminate\Validation\Rule;

class PermissionRequest extends Request
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
                    Rule::unique('permissions')->ignore($this->permission),
                ],
                'cn_name'      => 'required|between:3,25',
            ],
            'store' => [
                'name'         => 'required|between:3,25|regex:/^[0-9A-Za-z\-\_]+$/|unique:permissions',
                'cn_name'      => 'required|between:3,25',
                'roles'        => 'array',
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
            'name.unique'          => '权限标识已被占用，请重新填写。',
            'name.regex'           => '权限标识只支持数字、英文、横杠和下划线。',
            'name.between'         => '权限标识必须介于 3 - 25 个字符之间。',
            'name.required'        => '权限标识不能为空。',
            'cn_name.required'     => '权限中文名称不能为空。',
            'cn_name.between'      => '权限中文名称必须介于 3 - 25 个字符之间。',
            'roles.array'          => '角色数据提交的格式错误。',
            'ids.required'         => '批量删除时，缺乏必要参数',
            'ids.array'            => '批量删除时，数据提交的格式错误。',
        ];

        $messages = array_merge(parent::messages(), $messages);

        return $messages;
    }

}
