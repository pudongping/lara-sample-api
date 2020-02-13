<?php
/**
 * 抽奖
 *
 * Created by PhpStorm.
 * User: Alex
 * Date: 2020/2/12
 * Time: 12:37
 */

namespace App\Repositories\Common;

use App\Repositories\BaseRepository;
use App\Support\Code;
use App\Exceptions\ApiException;

class PrizeRepository extends BaseRepository
{

    public function prizeList()
    {
        // 奖品数组
        $proArr = array(
            // id => 奖品等级， name => 奖品名称, v => 奖品权重
            array('name' => '特等奖', 'weight' => 1),
            array('name' => '五等奖', 'weight' => 50),
            array('name' => '一等奖', 'weight' => 5),
            array('name' => '六等奖', 'weight' => 100),
            array('name' => '二等奖', 'weight' => 10),
            array('name' => '超级奖品', 'weight' => 0),
            array('name' => '没中奖', 'weight' => 500),
            array('name' => '四等奖', 'weight' => 22),
            array('name' => '三等奖', 'weight' => 12),
            array('name' => '七等奖', 'weight' => 200),
        );
        return $proArr;
    }

    /**
     * 前端抽奖
     *
     * @return mixed
     */
    public function lottery()
    {
        $regroupPrizeList = $this->regroupPrizeList();
        $keyAndWeight = $regroupPrizeList['keyAndWeight'];  // 奖品编号 => 奖品权重 数组
        $keyForGift = $regroupPrizeList['keyForGift'];  // 重新为奖品数组做编号

        $drawId = $this->getPrizeALG($keyAndWeight);  // 已经中奖的奖品编号

        $gift['yes'] = $keyForGift[$drawId];
        unset($keyForGift[$drawId]);  // 从原奖品数组中剔除已经中奖礼品
        shuffle($keyForGift);  // 打乱数组排序
        $gift['no'] = $keyForGift;

        return $gift;
    }

    /**
     * 模拟抽奖，验证概率
     *
     * @param $request
     * @return array
     */
    public function probably($request)
    {
        $times = $request->times ?? 10000;  // 模拟测试多少次，默认为 10000 次抽奖

        $regroupPrizeList = $this->regroupPrizeList();
        $keyAndWeight = $regroupPrizeList['keyAndWeight'];  // 奖品编号 => 奖品权重 数组
        $keyForGift = $regroupPrizeList['keyForGift'];  // 重新为奖品数组做编号

        $i = 0;
        $res = [];
        while ($i < $times) {
            $res[] = $this->getPrizeALG($keyAndWeight);
            $i++;
        }

        $result = array_count_values($res);  // 统计奖品出现次数

        foreach ($keyForGift as $key => &$val) {  // 将获奖次数追加到数组中
            $val['times'] = $result[$key] ?? 0;
        }

        return array_merge($keyForGift, []);
    }

    /**
     * 重组抽奖数据
     *
     * @return array|bool
     */
    public function regroupPrizeList()
    {
        // name => 奖品名称, weight => 奖品权重
//        $prizeList = [
//            ['name' => '超级奖品', 'weight' => 0],
//            ['name' => '特等奖', 'weight' => 1],
//            ['name' => '一等奖', 'weight' => 5],
//            ['name' => '二等奖', 'weight' => 10],
//            ['name' => '三等奖', 'weight' => 12],
//            ['name' => '四等奖', 'weight' => 22],
//            ['name' => '五等奖', 'weight' => 50],
//            ['name' => '六等奖', 'weight' => 100],
//            ['name' => '七等奖', 'weight' => 200],
//            ['name' => '八等奖', 'weight' => 200],
//            ['name' => '没中奖', 'weight' => 500],
//        ];
        $prizeList = $this->prizeList();  // 奖品权重数组
        if (!$prizeList) {
            throw new ApiException(Code::ERR_MODEL, [], '奖品数据不存在，不支持抽奖');
        }

        $keyAndWeight = [];  // 奖品编号 => 奖品权重 数组
        $keyForGift = [];
        foreach ($prizeList as $k => $value) {
            $keyForGift[$k + 1] = $value;  // 重新为奖品数组做编号
            $keyAndWeight[$k + 1] = intval($value['weight']);  // 奖品编号 => 奖品权重 数组
        }

        return compact('keyForGift', 'keyAndWeight');
    }

    /**
     * 抽奖算法
     *
     * @param array $proArr 奖品编号 => 奖品权重数组
     * @return int|string|null  中奖编号
     */
    public function getPrizeALG(array $proArr)
    {
        // 概率数组的总权重
        $proSum = array_sum($proArr);
        $rid = null;
        // 概率数组循环
        foreach ($proArr as $k => $proCur) {
            // 从 1 到概率总数中任意取值
            $randNum = mt_rand(1, $proSum);
            // 判断随机数是否在概率权重中
            if ($randNum <= $proCur) {
                // 取出奖品 id
                $rid = $k;
                break;
            } else {
                // 如果随机数不在概率权限中，则不断缩小总权重，直到从奖品数组中取出一个奖品
                $proSum -= $proCur;
            }
        }
        return $rid;
    }


}
