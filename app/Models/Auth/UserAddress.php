<?php

namespace App\Models\Auth;

use App\Models\Model;
use App\Models\Auth\User;

class UserAddress extends Model
{

    protected $fillable = [
        'province',
        'city',
        'district',
        'address',
        'zip',
        'contact_name',
        'contact_phone',
        'last_used_at',
    ];


    protected $dates = ['last_used_at'];

    /**
     * 地址和用户多对一关系
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'id', 'user_id');
    }

    /**
     * 获取完整的地址
     *
     * @return string
     */
    public function getFullAddressAttribute()
    {
        return "{$this->province}{$this->city}{$this->district}{$this->address}";
    }

}
