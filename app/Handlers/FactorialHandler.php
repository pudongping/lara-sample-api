<?php
/**
 * 使用阶乘原理，通过阶乘获取一个一维数组中全部的组合情况
 *
 * Created by PhpStorm.
 * User: Alex
 * Date: 2020/3/9
 * Time: 13:08
 */

namespace App\Handlers;


class FactorialHandler
{

    /**
     * 使用阶乘原理获取一维数组中全部的组合情况
     *
     * @param array $arr
     * @return array
     */
    public function getArrAllCombineByFactor(array $arr) : array
    {
        // 大于等于1 => n! = 1*2*3*4*5…………*(n-1)*n
        if (count($arr) > 1) {
            $combineArr = [];
            foreach ($arr as $k => $v) {
                // 除当前 key 以外的单元数组
                $temArr = $this->arrRmoveValueByKey($arr, $k);
                $sonCombineArr = $this->getArrAllCombineByFactor($temArr);
                foreach ($sonCombineArr as $value) {
                    $combineArr[] = $v . '|' . $value;
                }
            }
            return $combineArr;
        } else {
            return $arr;
        }
    }

    /**
     * 通过数组的 key 移除掉当前 key 所在的单元，返回除 key 单元以外的单元数组
     *
     * @param array $arr  原始数组
     * @param $k  需要移除数组单元的 key
     * @return array
     */
    public function arrRmoveValueByKey(array $arr, $k) : array
    {
        unset($arr[$k]);
        $arr = array_values($arr);
        return $arr;
    }

}
