<?php
/**
 * 日志记录
 *
 * Created by PhpStorm.
 * User: Alex
 * Date: 2020/2/16
 * Time: 21:42
 */

namespace App\Repositories\Admin\Setting;

use App\Repositories\BaseRepository;
use App\Models\Admin\Setting\Log;
use App\Support\TempValue;

class LogsRepository extends BaseRepository
{

    protected $model;

    public function __construct(Log $model)
    {
        $this->model = $model;
    }

    /**
     * 用户操作日志列表
     *
     * @param $request
     * @return array
     */
    public function getList($request)
    {
        $subQuery = \DB::raw('((select l.*,u.name,u.email,u.phone,u.avatar from logs as l right join users as u on l.user_id = u.id where l.guard_name = "api") 
        union all (select l.*,a.name,a.email,a.phone,a.avatar from logs as l right join admins as a on l.user_id = a.id where l.guard_name = "admin")) as aul');
        $model = \DB::table($subQuery);

        $search = $request->input('s');
        $model = $model->where(function ($query) use ($search) {
            if (!empty($search)) {
                $query->orWhere('name', 'like', '%' . $search . '%');
                $query->orWhere('email', 'like', '%' . $search . '%');
                $query->orWhere('phone', 'like', '%' . $search . '%');
                $query->orWhere('description', 'like', '%' . $search . '%');
                $query->orWhere('client_ip', 'like', '%' . $search . '%');
            }
        });

        if (false !== ($between = $this->searchTime($request))) {
            $model = $model->whereBetween('created_at', $between);
        }

        if (!is_null($request->guard_name)) {
            $model = $model->where('guard_name', $request->guard_name);
        }

        $paginator = $this->usePage($model);

        // 如果没有开启分页功能的话，则直接返回数据出去
        if (TempValue::$nopage) {
            return [config('fractal.collection_key') => $paginator->toArray()];
        }

        // 开启了分页功能，则还需要手动拼接一下分页数据集
        $data = $paginator->toArray();
        return [
            config('fractal.collection_key') => $paginator->getCollection()->toArray(),  // 分页后的数据
            'meta' => [
                'pagination' => [
                    'total' => $data['total'],
                    'count' => count($data['data']),
                    'per_page' => $data['per_page'],
                    'current_page' => $data['current_page'],
                    'total_pages' => $data['last_page'],
                    'links' => [
                        'previous' => $data['prev_page_url'],
                        'next' => $data['next_page_url'],
                    ]
                ]
            ]
        ];

    }

}
