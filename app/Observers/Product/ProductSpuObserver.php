<?php
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 2020/3/5
 * Time: 15:18
 */

namespace App\Observers\Product;

use App\Models\Product\ProductSpu;

class ProductSpuObserver
{

    public function saving(ProductSpu $productSpu)
    {
        // 修复 XSS 注入漏洞
        $productSpu->description = clean($productSpu->description, 'spu_description');
    }

}
