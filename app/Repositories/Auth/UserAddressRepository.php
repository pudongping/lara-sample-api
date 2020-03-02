<?php
/**
 * 收获地址相关
 *
 * Created by PhpStorm.
 * User: Alex
 * Date: 2020/2/22
 * Time: 23:36
 */

namespace App\Repositories\Auth;

use App\Repositories\BaseRepository;
use App\Models\Auth\UserAddress;

class UserAddressRepository extends BaseRepository
{
    protected $model;

    public function __construct(UserAddress $userAddressModel)
    {
        $this->model = $userAddressModel;
    }

    public function storage($request)
    {
        $input = $request->only(['province', 'city', 'district', 'address', 'zip', 'contact_name', 'contact_phone']);
        return $request->user()->addresses()->create($input);
    }

    public function modify($request)
    {
        $input = $request->only(['province', 'city', 'district', 'address', 'zip', 'contact_name', 'contact_phone']);
        return $request->user_address->update($input);
    }

}
