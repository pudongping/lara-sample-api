<?php
/**
 * 后台管理员
 *
 * Created by PhpStorm.
 * User: Alex
 * Date: 2020/2/11
 * Time: 13:54
 */

namespace App\Repositories\Auth;

use App\Repositories\BaseRepository;
use App\Models\Auth\Admin;
use App\Exceptions\ApiException;
use App\Support\Code;
use App\Models\Common\Image;
use App\Repositories\Auth\RolesRepository;

class AdminRepository extends BaseRepository
{
    protected $model;
    protected $imageModel;
    protected $rolesRepository;

    public function __construct(
        Admin $admin,
        Image $imageModel,
        RolesRepository $rolesRepository
    ) {
        $this->model = $admin;
        $this->imageModel = $imageModel;
        $this->rolesRepository = $rolesRepository;
    }

    /**
     * 管理员列表
     *
     * @param $request
     * @return mixed
     */
    public function getList($request)
    {
        $search = $request->input('s');
        $model = $this->model->where(function ($query) use ($search) {
            if (!empty($search)) {
                $query->orWhere('name', 'like', '%' . $search . '%');
                $query->orWhere('email', 'like', '%' . $search . '%');
                $query->orWhere('phone', 'like', '%' . $search . '%');
            }
        });

        if (false !== ($between = $this->searchTime($request))) {
            $model = $model->whereBetween('created_at', $between);
        }

        if (!is_null($request->state)) {
            $model = $model->where('state', intval(boolval($request->state)));
        }

        $model = $model->with('roles');

        return $this->usePage($model);
    }

    /**
     * 用户名/中国手机号/邮箱登录
     *
     * @param $request
     * @return array|bool
     * @throws ApiException
     */
    public function login($request)
    {
        $remeberMe = boolval($request->remember);
        $account = $request->account;
        $accountField = fetchAccountField($account);
        if ('name' === $accountField) {
            if (!validateUserName($account)) {
                Code::setCode(Code::ERR_PARAMS, null, ['账号需以字母开头，可以包括字母、数字、下划线、横杠']);
                return false;
            }
        }
        $credentials = [
            $accountField => $account,
            'password' => $request->password,
            'state' => Admin::STATE_NORMAL
        ];

        if ($remeberMe) {  // 勾选了「记住我」时
            $token = auth('admin')->setTTL(config('api.custom_jwt.remember_me_ttl'))->attempt($credentials);
        } else {
            $token = auth('admin')->attempt($credentials);
        }

        if (!$token) {
            throw new ApiException(Code::ERR_PARAMS, ['账号或密码错误']);
        }

        return $this->respondWithToken($token);
    }

    /**
     * 添加用户-数据处理
     *
     * @param $request
     * @return mixed
     * @throws \Exception
     */
    public function storage($request)
    {
        $allowRoles = $this->rolesRepository->validateRoles($request->roles);

        $input = $request->only(['name', 'email', 'phone']);
        $input['password'] = \Hash::make($request->password);

        if ($request->avatar_image_id) {
            $image = $this->imageModel->find($request->avatar_image_id);
            $input['avatar'] = $image->path;
        }

        $input['state'] = is_null($request->state) ? Admin::STATE_NORMAL : intval(boolval($request->state));

        \DB::beginTransaction();
        try {
            $user = $this->store($input);
            // 赋予角色
            if ($allowRoles) $user->assignRole($allowRoles);
            user_log('添加用户「' . $user->name . '」');
            \DB::commit();
        } catch (\Exception $exception) {
            \DB::rollBack();
        }

        return $user;
    }

    /**
     * 修改登录用户信息
     *
     * @param $request
     * @return mixed
     */
    public function modify($request)
    {
        $allowRoles = $this->rolesRepository->validateRoles($request->roles);

        $input = $request->only(['name', 'email', 'phone']);
        if ($request->avatar_image_id) {
            $image = $this->imageModel->find($request->avatar_image_id);
            $input['avatar'] = $image->path;
        }

        if ($request->new_password) {
            if (\Hash::check($request->current_password, $request->user->password)) {
                $input['password'] = bcrypt($request->new_password);
            } else {
                throw new ApiException(Code::ERR_PARAMS, ['当前密码错误']);
            }
        }

        $input['state'] = is_null($request->state) ? Admin::STATE_NORMAL : intval(boolval($request->state));

        \DB::beginTransaction();
        try {
            $user = $this->update($request->user->id, $input);  // $request->user 获取的是路由参数 user 隐式实例
            // 赋予角色
            if ($allowRoles) $user->assignRole($allowRoles);
            user_log('修改用户「' . $user->name . '」的信息为：' . json_encode($input, 256));
            \DB::commit();
        } catch (\Exception $exception) {
            \DB::rollBack();
        }

        return $user;
    }

    /**
     * 删除管理员
     *
     * @param $user
     * @return bool
     */
    public function destroy($user)
    {
        if (Admin::ADMIN_ID === $user->id) {
            Code::setCode(Code::ERR_PERM, '当前用户为系统管理员，不允许删除');
            return false;
        }
        $user->delete();
    }

    /**
     * 刷新 token
     *
     * @return array
     */
    public function refreshToken()
    {
        return $this->respondWithToken(auth('admin')->refresh());
    }

    /**
     * 删除 token （退出登录）
     */
    public function logout()
    {
        auth('admin')->logout();
    }

    /**
     * 获取令牌数组结构
     *
     * @param $token 令牌 token
     * @return array
     */
    protected function respondWithToken($token)
    {
        return [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => auth('admin')->factory()->getTTL() * 60,  // 单位为秒，3600s
        ];
    }

}
