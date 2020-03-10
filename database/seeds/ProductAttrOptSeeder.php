<?php

use Illuminate\Database\Seeder;
use App\Models\Product\ProductAttribute;
use App\Models\Product\ProductAttributeOption;
use App\Models\Product\ProductSku;

class ProductAttrOptSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ProductAttribute::truncate();
        ProductAttributeOption::truncate();
        ProductSku::truncate();

        $attrOptArr = [
            ['attr' => '颜色', 'attr_sort' => 15, 'options' => ['黑色', '白色', '咖啡色'], 'opt_sort' => [10, 20, 3]],
            ['attr' => '尺寸', 'attr_sort' => 17, 'options' => ['S', 'M', 'L'], 'opt_sort' => [4, 2, 9]],
            ['attr' => '材质', 'attr_sort' => 7, 'options' => ['羽绒', '鸭绒'], 'opt_sort' => [10, 2]]
        ];

        foreach ($attrOptArr as $value) {
            $attrRes = ProductAttribute::create(
                [
                    'spu_id' => 1,
                    'name' => $value['attr'],
                    'sort' => $value['attr_sort']
                ]
            );
            foreach ($value['options'] as $kk => $vv) {
                ProductAttributeOption::create([
                    'attribute_id' => $attrRes->id,
                    'name' => $vv,
                    'sort' => $value['opt_sort'][$kk],
                ]);
            }
        }

        $skuArr = [
            ['spu_id' => 1, 'main_url' => '', 'price' => 10, 'stock' => 20, 'key_attr_option' => '3_8|1_3|2_5|'],
            ['spu_id' => 1, 'main_url' => '', 'price' => 25, 'stock' => 80, 'key_attr_option' => '3_8|1_3|2_4|'],
            ['spu_id' => 1, 'main_url' => 'iamge.png', 'price' => 10.84, 'stock' => 60, 'key_attr_option' => '3_8|1_3|2_6|'],
            ['spu_id' => 1, 'main_url' => '', 'price' => 145.99, 'stock' => 40, 'key_attr_option' => '3_8|1_1|2_5|'],
            ['spu_id' => 1, 'main_url' => '', 'price' => 40, 'stock' => 0, 'key_attr_option' => '3_8|1_1|2_4|'],
            ['spu_id' => 1, 'main_url' => '', 'price' => 70, 'stock' => 60, 'key_attr_option' => '3_8|1_1|2_6|'],
            ['spu_id' => 1, 'main_url' => '', 'price' => 10.34, 'stock' => 70, 'key_attr_option' => '3_8|1_2|2_5|'],
            ['spu_id' => 1, 'main_url' => 'aa.png', 'price' => 15, 'stock' => 80, 'key_attr_option' => '3_8|1_2|2_4|'],
            ['spu_id' => 1, 'main_url' => '', 'price' => 40, 'stock' => 0, 'key_attr_option' => '3_8|1_2|2_6|'],
            ['spu_id' => 1, 'main_url' => '', 'price' => 60, 'stock' => 20, 'key_attr_option' => '3_7|1_3|2_5|'],
            ['spu_id' => 1, 'main_url' => '', 'price' => 30, 'stock' => 0, 'key_attr_option' => '3_7|1_3|2_4|'],
            ['spu_id' => 1, 'main_url' => 'bb.png', 'price' => 350, 'stock' => 0, 'key_attr_option' => '3_7|1_3|2_6|'],
            ['spu_id' => 1, 'main_url' => '', 'price' => 7980.7, 'stock' => 0, 'key_attr_option' => '3_7|1_1|2_5|'],
            ['spu_id' => 1, 'main_url' => '', 'price' => 410, 'stock' => 90, 'key_attr_option' => '3_7|1_1|2_4|'],
            ['spu_id' => 1, 'main_url' => '', 'price' => 128.97, 'stock' => 450, 'key_attr_option' => '3_7|1_1|2_6|'],
            ['spu_id' => 1, 'main_url' => 'cc.png', 'price' => 148, 'stock' => 46, 'key_attr_option' => '3_7|1_2|2_5|'],
            ['spu_id' => 1, 'main_url' => '', 'price' => 16.75, 'stock' => 0, 'key_attr_option' => '3_7|1_2|2_4|'],
            ['spu_id' => 1, 'main_url' => '', 'price' => 129, 'stock' => 540, 'key_attr_option' => '3_7|1_2|2_6|'],
        ];

        ProductSku::insert($skuArr);

    }
}
