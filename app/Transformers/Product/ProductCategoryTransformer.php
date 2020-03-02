<?php

namespace App\Transformers\Product;

use App\Transformers\BaseTransformer;

class ProductCategoryTransformer extends BaseTransformer
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
            'pid' => $resource->pid,
            'name' => $resource->name,
            'description' => $resource->description,
            'sort' => $resource->sort,
            'status' => $resource->status,
            'level' => $resource->level,
            'path' => $resource->path,
            'path_ids' => $resource->path_ids,
            'created_at' => $resource->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $resource->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
