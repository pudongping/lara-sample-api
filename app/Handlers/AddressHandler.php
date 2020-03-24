<?php
/**
 * 地址处理
 *
 * Created by PhpStorm.
 * User: Alex
 * Date: 2020/3/24
 * Time: 20:40
 */

namespace App\Handlers;

use App\Exceptions\ApiException;
use App\Support\Code;

class AddressHandler
{
    protected $baiduAk;
    protected $mapUrl = 'http://api.map.baidu.com/reverse_geocoding/v3';

    public function __construct()
    {
        $this->baiduAk = config('api.baidu.map_ak');
    }

    /**
     * 通过经纬度获取详细地址
     * @link https://lbsyun.baidu.com/index.php?title=jspopular/guide/geocoding
     *
     * @param $request
     * @return array
     * @throws ApiException
     */
    public function fetchAddressByLngLat($request)
    {
        $args = [
            'ak' => $this->baiduAk,
            'output' => 'json',
            'coordtype' => 'wgs84ll',  // 坐标类型：（wgs84ll即GPS经纬度）
            'location' => $request->lat . ',' . $request->lng
        ];
        // http://api.map.baidu.com/reverse_geocoding/v3?ak=xKApo7jnXbE67RZir7WF8Ie26nTiaVyg&output=json&coordtype=wgs84ll&location=31.225696563611,121.49884033194
        $result = http_get($this->mapUrl, $args);

        // 百度地图接口出错时
        if (0 !== $result['status']) throw new ApiException(Code::ERR_BAIDU_ADDRESS);

        $data = [
            'province' => $result['result']['addressComponent']['province'],
            'city' => $result['result']['addressComponent']['city'],
            'district' => $result['result']['addressComponent']['district'],
            'address' => $result['result']['addressComponent']['street'] . $result['result']['addressComponent']['street_number'],
            'zip' => $result['result']['addressComponent']['adcode'],  // 邮编
            'formatted_address' => $result['result']['formatted_address'],
        ];

        return $data;
    }

}
