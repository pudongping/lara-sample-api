<?php
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 2020/3/1
 * Time: 22:49
 */

namespace App\Observers\Product;

use App\Models\Product\ProductCategory;

class ProductCategoryObserver
{
    /**
     * 监听 ProductCategory 的创建事件，用于初始化 path 和 level 字段值
     *
     * @param ProductCategory $productCategory
     */
    public function creating(ProductCategory $productCategory)
    {
        $this->makeLevelAndPath($productCategory);
    }

    /**
     * 更新前，设置类目的层级和 ’路径‘
     *
     * @param ProductCategory $productCategory
     */
    public function updating(ProductCategory $productCategory)
    {
        $this->makeLevelAndPath($productCategory);
    }

    /**
     * 删除类目后，子集类目也需要删除
     *
     * @param ProductCategory $productCategory
     */
    public function deleted(ProductCategory $productCategory)
    {
        // 删除类目之后，也需要将 子类目 都删除
        ProductCategory::where('pid', $productCategory->id)->delete();
    }

    private function makeLevelAndPath($productCategory)
    {
        // 如果创建的是一个根类目
        if (!$productCategory->pid) {
            // 将层级设为 0
            $productCategory->level = 0;
            // 将 path 设为 -
            $productCategory->path = '-';
        } else {
            // 将层级设为父类目的层级 +1
            $productCategory->level = $productCategory->parent->level + 1;
            // 将 path 值设为父类目的 path 追加父类目 ID 以及最后跟上一个 - 分隔符
            $productCategory->path = $productCategory->parent->path . $productCategory->pid . '-';
        }
    }


}
