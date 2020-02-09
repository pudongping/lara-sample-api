<?php

namespace App\Transformers\Auth;

use App\Transformers\BaseTransformer;

class UserTransformer extends BaseTransformer
{
    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected $availableIncludes = [];

    /**
     * List of resources to automatically include
     *
     * @var array
     */
    protected $defaultIncludes = [];

    /**
     * Transform object into a generic array
     *
     * @var $resource
     * @return array
     */
    public function transform($resource)
    {
        return [

            'id' => $resource->id,
            'social_type' => $resource->social_type,
            'name' => $resource->name,
            'phone' => $resource->phone,
            'email' => $resource->email,
            'nickname' => $resource->nickname,
            'sex' => $resource->sex,
            'city' => $resource->city,
            'province' => $resource->province,
            'country' => $resource->country,
            'avatar' => $resource->avatar,
            'headimgurl' => $resource->headimgurl,
            'created_at' => $resource->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $resource->updated_at->format('Y-m-d H:i:s'),

        ];
    }
}
