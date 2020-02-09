<?php
/**
 * 上传图片
 *
 * Created by PhpStorm.
 * User: Alex
 * Date: 2020/2/9
 * Time: 23:34
 */

namespace App\Repositories\Common;

use App\Repositories\BaseRepository;
use App\Models\Common\Image;
use App\Handlers\ImageUploadHandler;

class ImageRepository extends BaseRepository
{

    protected $model;
    protected $imageUploadHandler;

    public function __construct(
        Image $image,
        ImageUploadHandler $imageUploadHandler
    ) {
        $this->model = $image;
        $this->imageUploadHandler = $imageUploadHandler;
    }

    /**
     * 上传图片
     *
     * @param $request
     * @return mixed
     */
    public function storage($request)
    {
        $user = $request->user();
        $size = 'avatar' == $request->type ? 416 : 1024;
        $types = \Str::plural($request->type);  // 单词转成复数形式
        $result = $this->imageUploadHandler->save($request->image, $types, $user->id, 'image', $size);

        $input = [
            'user_id' => $user->id,
            'type' => $request->type,
            'path' => $result['relativePath']
        ];

        return $this->store($input);
    }

}
