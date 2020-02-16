<?php

use Illuminate\Database\Seeder;
use App\Models\Admin\Setting\Menu;

class MenusTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $routes = collect(\Route::getRoutes())->map(function ($route) {
            return $route->getName();
        })->filter(function ($route) {
            return 'a' == $route[0];  // 只保留 a 开头的路由名称
        })->all();
        // 所有的路由
        $allRoutes = array_values($routes);

        $state = array_keys(Menu::$state);

        // 获取 Faker 实例
        $faker = app(Faker\Generator::class);

        $menus = factory(Menu::class)
            ->times(50)
            ->make()
            ->each(function (
                $menu,
                $index
            ) use (
                $faker,
                $allRoutes,
                $state
            ) {
                // 从数组中随机取出一个并赋值
                $menu->state = $faker->randomElement($state);
                $menu->type = $faker->randomElement($state);
                $menu->route_name = $faker->randomElement($allRoutes);
            });

        // 将数据集合转换为数组，并插入到数据库中
        Menu::insert($menus->toArray());
    }
}
