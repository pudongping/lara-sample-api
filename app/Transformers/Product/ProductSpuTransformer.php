<?php

namespace App\Transformers\Product;

use App\Transformers\BaseTransformer;

class ProductSpuTransformer extends BaseTransformer
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
            'category_ids' => $resource->category_ids,
            'brand_id' => $resource->brand_id,
            'title' => $resource->title,
            'unit' => $resource->unit,
            'sketch' => $resource->sketch,
            'keywords' => $resource->keywords,
            'tags' => $resource->tags,
            'barcode' => $resource->barcode,
            'price' => $resource->price,
            'market_price' => $resource->market_price,
            'rating' => $resource->rating,
            'virtual_retail_num' => $resource->virtual_retail_num,
            'warning_stock' => $resource->warning_stock,
            'main_image' => $resource->main_image,
            'slider_image' => $resource->slider_image,
            'description' => (string)$resource->description,
            'status' => $resource->status,
            'sort' => $resource->sort,
            'categories' => $resource->categories,
            'brand' => $resource->brand,

        ];
    }
}
