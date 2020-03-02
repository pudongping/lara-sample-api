<?php

use Illuminate\Database\Seeder;
use App\Models\Product\ProductBrand;
use App\Models\Product\ProductCategory;

class ProductBrandsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
         $brands = [
             ['name' => '金士顿'],
             ['name' => '联想'],
             ['name' => '三星'],
             ['name' => '诺基亚'],
             ['name' => '飞科'],
             ['name' => 'Apple'],
             ['name' => '美的'],
         ];
         ProductBrand::insert($brands);

         $cateIds = ProductCategory::all()->pluck('id')->toArray();
         $brandIds = ProductBrand::all()->pluck('id')->toArray();

         $data = [];
         $item = [];
         for ($i = 0; $i < 30; $i++) {
             $item['category_id'] = \Arr::random($cateIds);
             $item['brand_id'] = \Arr::random($brandIds);
             $data[] = $item;
         }

        \DB::table('product_categories_pivot_brands')->insert($data);

    }
}
