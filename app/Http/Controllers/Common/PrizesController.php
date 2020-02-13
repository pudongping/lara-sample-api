<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\Common\PrizeRepository;

class PrizesController extends Controller
{

    protected $prizeRepository;

    public function __construct(PrizeRepository $prizeRepository)
    {
        $this->init();
        $this->prizeRepository = $prizeRepository;
    }

    /**
     * 抽奖
     *
     * @return mixed
     */
    public function lottery()
    {
        $data = $this->prizeRepository->lottery();
        return $this->response->send($data);
    }

    /**
     * 模拟抽奖，验证概率
     *
     * @param Request $request
     * @return mixed
     */
    public function probably(Request $request)
    {
        $data = $this->prizeRepository->probably($request);
        return $this->response->send($data);
    }

}
