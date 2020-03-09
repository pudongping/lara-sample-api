<?php
/**
 * 递归计算笛卡尔乘积
 *
 * Created by PhpStorm.
 * User: Alex
 * Date: 2020/3/5
 * Time: 20:35
 */

namespace App\Handlers;


class CarteSianHandler
{
    /**
     * 保存结果
     *
     * @var array
     */
    public $carteSian = [];

    /**
     * 计算笛卡尔乘积的结果
     *
     * @param array $params
     * @param array $temporary
     * @return array
     */
    public function carteSian(array $params, array $temporary = [])
    {
        if (empty($params)) return [];
        foreach (array_shift($params) as $param) {
            array_push($temporary, $param);
            $params ? $this->carteSian($params, $temporary) : array_push($this->carteSian, $temporary);
            array_pop($temporary);
        }
    }

    /**
     * 获取笛卡尔乘积的结果
     *
     * @param array $params
     * @return array
     */
    public function getCarteSianData(array $params) : array
    {
        $this->carteSian($params);
        return $this->carteSian;
    }

}
