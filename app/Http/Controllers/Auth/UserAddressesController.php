<?php
/**
 * 收获地址相关
 *
 * Created by PhpStorm.
 * User: Alex
 * Date: 2020/2/22
 * Time: 23:36
 */

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\Auth\UserAddressRepository;
use App\Models\Auth\UserAddress;
use App\Http\Requests\Auth\UserAddressRequest;

class UserAddressesController extends Controller
{

    protected $userAddressRepository;

    public function __construct(UserAddressRepository $userAddressRepository)
    {
        $this->init();
        $this->userAddressRepository = $userAddressRepository;
    }

    public function index(Request $request)
    {
        return $this->response->send($request->user()->addresses);
    }

    public function store(UserAddressRequest $request)
    {
        $data = $this->userAddressRepository->storage($request);
        return $this->response->send($data);
    }

    public function edit(UserAddress $user_address)
    {
        $this->authorize('ownPolicy', $user_address);

        return $this->response->send($user_address);
    }

    public function update(UserAddress $user_address, UserAddressRequest $request)
    {
        $this->authorize('ownPolicy', $user_address);

        $data = $this->userAddressRepository->modify($request);
        return $this->response->send($data);
    }

    public function destroy(UserAddress $user_address)
    {
        $this->authorize('ownPolicy', $user_address);

        $user_address->delete();
        return $this->response->send();
    }

}
