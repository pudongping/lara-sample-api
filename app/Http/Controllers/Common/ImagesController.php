<?php
/**
 * 图片上传
 *
 * Created by PhpStorm.
 * User: Alex
 * Date: 2020/2/9
 * Time: 23:03
 */

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Repositories\Common\ImageRepository;
use App\Http\Requests\Common\ImageRequest;

class ImagesController extends Controller
{

    protected $imageRepository;

    public function __construct(ImageRepository $imageRepository)
    {
        $this->init();
        $this->imageRepository = $imageRepository;
    }

    /**
     * 上传图片
     *
     * @param ImageRequest $request
     * @return mixed
     */
    public function store(ImageRequest $request)
    {
        $data = $this->imageRepository->storage($request);
        return $this->response->send($data);
    }

}
