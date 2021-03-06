<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Support\TempValue;

class Request extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * 获取已定义验证规则的错误消息
     *
     * @return array
     */
    public function messages()
    {
        return [
            'required'       => '缺少必要参数 [:attribute]，请检查',
            'string'         => '参数 [:attribute] 格式错误，必须为字符串',
            'integer'        => '参数 [:attribute] 格式错误，必须为整数',
            'unique'         => '当前 [:attribute] 数据已存在，请检查',
            'phone.unique'   => '该手机号已存在',
            'exists'         => '[:attribute] 所在记录不存在',
            'max'            => '参数 [:attribute] 超过长度限制',
            'min'            => '参数 [:attribute] 小于长度限制',
            'mimes'          => '[:attribute] 文件类别不匹配，期望文件为：:values',
            'password.regex' => '密码过于简单，要求8位以上，并且含有英文数字或符号',
            'password.min'   => '密码长度过短',
            'regex'          => '数据格式不正确',
            'ip'             => 'IP地址格式不正确',
            'phone.size'     => '手机号长度不正确，请输入11位',
        ];
    }

    /**
     * 根据请求 method 自动选中所使用的 rule
     *
     * @param $rules
     * @return array
     */
    protected function useRule($rules)
    {
        if (array_key_exists(TempValue::$action, $rules)) {
            return $rules[TempValue::$action];
        } else {
            return [];
        }
    }


}
