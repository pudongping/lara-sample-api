<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();


        // 策略自动发现
        // @link https://learnku.com/docs/laravel/6.x/authorization/5153#creating-policies
        Gate::guessPolicyNamesUsing(function ($modelClass) {
            // 动态返回模型对应的策略名称，如：// 'App\Models\Auth\User' => 'App\Policies\Auth\UserPolicy',
            // 返回策略类名…
            return str_replace('Models', 'Policies', $modelClass) . 'Policy';
        });

    }
}
