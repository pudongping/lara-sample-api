<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Auth\User;
use App\Models\Auth\Admin;

class GenerateToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sample:generate-token';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '为用户生成一年有效期的 access_token';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $allowedGuards = array_keys(config('auth.guards'));
        $guard = $this->ask('请输入你想为哪个守卫创建 token ？可选值有：'. join('|', $allowedGuards));
        if (!in_array($guard, $allowedGuards)) {
            return $this->error('输入的守卫名称不合法！');
        }

        $userId = $this->ask('输入用户 id');

        $user = null;
        if ('api' == $guard) {
            $user = User::find($userId);
         } elseif ('admin' == $guard) {
            $user = Admin::find($userId);
        }

        if (!$user) {
            return $this->error('用户不存在');
        }

        // 一年以后过期
        $ttl = 365*24*60;

        if ('api' == $guard) {
            $this->info(auth('api')->setTTL($ttl)->login($user));
        } elseif ('admin' == $guard) {
            $this->info(auth('admin')->setTTL($ttl)->login($user));
        } else {
            $this->error('生成 token 失败！');
        }

    }
}
