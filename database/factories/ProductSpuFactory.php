<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model;
use Faker\Generator as Faker;
use App\Models\Product\ProductSpu;

$factory->define(ProductSpu::class, function (Faker $faker) {

    $images = [
        'https://img.alicdn.com/imgextra/i4/45804343/O1CN01uki2IW1hx8hWacpOC_!!0-saturn_solar.jpg_250x250.jpg',
        'https://img.alicdn.com/imgextra/i1/116371118/O1CN01BeQo5n1K85NvVf9xP_!!0-saturn_solar.jpg_250x250.jpg',
    ];

    $image = $faker->randomElement([
        'https://g-search1.alicdn.com/img/bao/uploaded/i4/i3/125353333/O1CN01xGxQAF1aUYfasTDk3_!!125353333.jpg_250x250.jpg_.webp',
        'https://g-search3.alicdn.com/img/bao/uploaded/i4/i2/2468001212/O1CN01HFTOZi1Kp8VsTWyXF_!!0-item_pic.jpg_250x250.jpg_.webp',
        'https://g-search1.alicdn.com/img/bao/uploaded/i4/i4/671012022/O1CN01mQZOSt1Qo7QQlyZgx_!!671012022.jpg_250x250.jpg_.webp',
        'https://img.alicdn.com/imgextra/i4/45804343/O1CN01uki2IW1hx8hWacpOC_!!0-saturn_solar.jpg_250x250.jpg',
        'https://img.alicdn.com/imgextra/i1/116371118/O1CN01BeQo5n1K85NvVf9xP_!!0-saturn_solar.jpg_250x250.jpg',
    ]);

    return [
        'category_ids' => [10, 11, 12, 13],
        'brand_id' => $faker->numberBetween(1, 4),
        'title' => $faker->word,
        'unit' => '个',
        'sketch' => $faker->word,
        'keywords' => '知足|长乐|舒适',
        'tags' => '便宜|实惠|耐用',
        'barcode' => \Str::random(10),
        'price' => 12.45,
        'market_price' => 99.99,
        'rating' => $faker->numberBetween(0, 5),
        'sold_count' => $faker->numberBetween(50, 1000),
        'review_count' => $faker->numberBetween(50, 1000),
        'virtual_retail_num' => $faker->numberBetween(100, 500),
        'description' => $faker->sentence,
        'stock' => $faker->numberBetween(100, 500),
        'warning_stock' => $faker->numberBetween(100, 500),
        'main_image' => $image,
        'slider_image' => $images,
        'status' => $faker->numberBetween(1, 4),
        'sort' => $faker->numberBetween(50, 1000),
    ];

});
