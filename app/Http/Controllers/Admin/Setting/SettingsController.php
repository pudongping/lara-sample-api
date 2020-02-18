<?php

namespace App\Http\Controllers\Admin\Setting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin\Setting\Setting;

class SettingsController extends Controller
{

    public function __construct()
    {
        $this->init();
    }

    /**
     * 站点设置列表
     *
     * @return mixed
     */
    public function index()
    {

        if (\Cache::has(config('api.cache_key.site'))) {
            $data = \Cache::get(config('api.cache_key.site'));
        } else {
            $data = Setting::orderBy('sort', 'asc')->get()->toArray();
            \Cache::put(config('api.cache_key.site'), $data);
        }

        return $this->response->send($data);
    }

    /**
     * 更新站点设置
     *
     * @param Request $request
     * @return mixed
     */
    public function update(Request $request)
    {
        $params = $request->settiings;
        if ($params) {
            $where = ['`key`' => array_keys($params)];
            $needUpdateFields = ['`value`' => array_values($params)];
            batchUpdate('settings', $where, $needUpdateFields);

            \Cache::forget(config('api.cache_key.site'));
        }
        return $this->response->send();
    }


    /**
     * 清空所有缓存
     *
     * @return mixed
     */
    public function clearCache()
    {
        \Cache::flush();
        return $this->response->send();
    }

}
