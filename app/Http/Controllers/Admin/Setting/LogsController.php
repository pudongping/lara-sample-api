<?php

namespace App\Http\Controllers\Admin\Setting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\Admin\Setting\LogsRepository;

class LogsController extends Controller
{

    protected $logsRepository;

    public function __construct(LogsRepository $logsRepository)
    {
        $this->init();
        $this->logsRepository = $logsRepository;
    }

    /**
     * 用户操作日志列表
     *
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $data = $this->logsRepository->getList($request);
        return $this->response->send($data);
    }

}
