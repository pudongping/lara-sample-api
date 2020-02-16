<?php

namespace App\Http\Requests\Admin\Setting;

use App\Http\Requests\Request;

class MenuRequest extends Request
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules =  [
            'store' => [
                'route_name' => 'required|string',
                'cn_name' => 'max:100',
                'permission' => 'array',
                'icon' => 'max:100|string',
                'sort' => 'integer',
                'state' => 'integer',
                'type' => 'integer',
            ],
            'update' => [
                'route_name' => 'required|string',
                'cn_name' => 'max:100',
                'permission' => 'array',
                'icon' => 'max:100|string',
                'sort' => 'integer',
                'state' => 'integer',
                'type' => 'integer',
            ],
        ];

        return $this->useRule($rules);
    }

    public function attributes()
    {
        return [
            'cn_name' => '路由中文名称',
            'sort' => '排序编号',
            'icon' => '图标',
        ];
    }

    public function messages()
    {
        $messages = [
            'route_name.required' => '路由不能为空',
            'route_name.string' => '路由必须为字符串',
            'permission.array' => '权限数据提交格式错误'
        ];

        $messages = array_merge(parent::messages(), $messages);

        return $messages;
    }

}
