<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Faker\Generator as Faker;
use App\Models\Admin\Setting\Menu;

$factory->define(Menu::class, function (Faker $faker) {

    $sentence = $faker->sentence();

    // 随机取一个月以内的时间
    $updated_at = $faker->dateTimeThisMonth();

    // 传参为生成最大时间不超过，因为创建时间需永远比更改时间要早
    $created_at = $faker->dateTimeThisMonth($updated_at);

    return [
        'extra' => $sentence,
        'description' => $faker->text(), // 生成大段的随机文本
        'cn_name' => $sentence,
        'created_at' => $created_at,
        'updated_at' => $updated_at,
    ];

});
