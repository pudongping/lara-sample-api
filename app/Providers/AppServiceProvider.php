<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Product\ProductCategory;
use App\Observers\Product\ProductCategoryObserver;
use App\Models\Product\ProductSpu;
use App\Observers\Product\ProductSpuObserver;
use App\Models\Product\ProductSku;
use App\Observers\Product\ProductSkuObserver;
use App\Models\Order\Order;
use App\Observers\Order\OrderObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // 非 production 开发环境才注册以下服务
        if ($this->app->environment() !== 'production') {
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // 注册观察者

        ProductCategory::observe(ProductCategoryObserver::class);
        ProductSpu::observe(ProductSpuObserver::class);
        ProductSku::observe(ProductSkuObserver::class);
        Order::observe(OrderObserver::class);

    }
}
