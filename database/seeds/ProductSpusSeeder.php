<?php

use Illuminate\Database\Seeder;
use App\Models\Product\ProductSpu;

class ProductSpusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $productSpus = factory(ProductSpu::class, 30)->create();
        foreach ($productSpus as $spus) {
            $spus->categories()->attach([10, 11, 12, 13]);
        }
    }
}
