<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\Request;

class UserAddressRequest extends Request
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
                'province'      => 'required',
                'city'          => 'required',
                'district'      => 'required',
                'address'       => 'required',
                'zip'           => 'required',
                'contact_name'  => 'required',
                'contact_phone' => 'required',
            ],
            'update' => [
                'province'      => 'required',
                'city'          => 'required',
                'district'      => 'required',
                'address'       => 'required',
                'zip'           => 'required',
                'contact_name'  => 'required',
                'contact_phone' => 'required',
            ],
        ];

        return $this->useRule($rules);
    }

    public function attributes()
    {
        return [
            'province'      => '省份',
            'city'          => '城市',
            'district'      => '区域',
            'address'       => '详细地址',
            'zip'           => '邮编',
            'contact_name'  => '联系人姓名',
            'contact_phone' => '联系人电话',
        ];
    }


}
