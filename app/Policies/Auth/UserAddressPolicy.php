<?php

namespace App\Policies\Auth;

use App\Models\Auth\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Models\Auth\UserAddress;

class UserAddressPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function ownPolicy(User $currentUser, UserAddress $userAddress)
    {
        return $currentUser->id === $userAddress->user_id;
    }

}
