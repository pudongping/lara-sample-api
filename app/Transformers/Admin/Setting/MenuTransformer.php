<?php

namespace App\Transformers\Admin\Setting;

use App\Transformers\BaseTransformer;

class MenuTransformer extends BaseTransformer
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
            'route_name' => $resource->route_name,
            'cn_name' => $resource->cn_name,
            'permission' => $resource->permission,
            'icon' => $resource->icon,
            'extra' => $resource->extra,
            'description' => $resource->description,
            'sort' => $resource->sort,
            'state' => $resource->state,
            'type' => $resource->type,
            'created_at' => $resource->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $resource->updated_at->format('Y-m-d H:i:s'),

        ];
    }
}
