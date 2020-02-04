<?php
/**
 * 用户相关操作
 *
 * Created by PhpStorm.
 * User: Alex
 * Date: 2020/2/4
 * Time: 20:48
 */

namespace App\Repositories\Auth;

use App\Repositories\BaseRepository;
use App\Models\Auth\User;
use App\Support\Code;
use App\Exceptions\ApiException;

class UserRepository extends BaseRepository
{

    protected $model;

    public function __construct(User $user)
    {
        $this->model = $user;
    }

    /**
     * 注册
     * 支持用户名「/^[a-zA-Z]([-_a-zA-Z0-9]{3,20})+$/」、中国手机号、邮箱三种账号方式
     *
     * @param $request
     * @return bool|mixed
     * @throws ApiException
     */
    public function register($request)
    {
         $account = $request->account;
         $accountField = fetchAccountField($account);
         if ('name' === $accountField) {
             if (!validateUserName($account)) {
                 Code::setCode(Code::ERR_PARAMS, null, ['账号需以字母开头，可以包括字母、数字、下划线、横杠']);
                 return false;
             }
         }
         $item = $this->getSingleRecord($account, $accountField, false);
         if ($item) throw new ApiException(Code::ERR_USER_EXIST);

         $input = [
             $accountField => $account,
             'password' => bcrypt($request->password)
         ];
         $user = $this->store($input);

         return $user;
    }

}
