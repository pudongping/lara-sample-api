<?php

namespace App\Transformers\Auth;

use League\Fractal;
use League\Fractal\TransformerAbstract;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;


class RoleTransformer extends TransformerAbstract
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
            'name' => $resource->name,
            'cn_name' => $resource->cn_name,
            'created_at' => (string)$resource->created_at,
            'updated_at' => (string)$resource->updated_at,
			
        ];
    }
}
