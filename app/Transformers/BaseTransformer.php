<?php
/**
 *  base transformer
 * https://packagist.org/packages/cyvelnet/laravel5-fractal#v2.3.0
 * https://packalyst.com/packages/package/cyvelnet/laravel5-fractal
 *
 * Created by PhpStorm.
 * User: Alex
 * Date: 2020/2/3
 * Time: 14:28
 */

namespace App\Transformers;

use League\Fractal;
use League\Fractal\TransformerAbstract;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;


class BaseTransformer extends TransformerAbstract
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
        return [];
    }
}
