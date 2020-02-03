<?php
/**
 * 封装 transformer 插件方法
 * https://packagist.org/packages/cyvelnet/laravel5-fractal#v2.3.0
 * https://packalyst.com/packages/package/cyvelnet/laravel5-fractal
 *
 * Created by PhpStorm.
 * User: Alex
 * Date: 2020/2/3
 * Time: 14:35
 */

namespace App\Support;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use League\Fractal\TransformerAbstract;
use App\Transformers\BaseTransformer;
use Fractal;

class Transformer
{

    /**
     * 指定显示 transformer 中的字段
     *
     * @param $fields array|string 只需显示字段 [id,client_ip,created_at] 或者 'id,client_ip,created_at'
     */
    public function fieldsets($fields)
    {
        if(is_array($fields)){
            $fields = join(',', $fields);
        }

        Fractal::fieldsets([config('fractal.collection_key') => $fields]);
    }

    /**
     * 添加元数据
     *
     * @param array $metaData => ['key1' => 'data1', 'key2' => 'data2']
     */
    public function addMeta(array $metaData)
    {
        Fractal::addMeta($metaData);
    }

    /**
     * 转换单条记录
     *
     * @param $data  Eloquent 单条数据
     * @param TransformerAbstract|null  $transformer 自定义的 transformer 类，为 null 时，则自动获取模型相关的 transformer 类
     * @return mixed  经过 transformer 转换过的单条数据
     * @throws \Exception
     */
    public function item($data, TransformerAbstract $transformer = null)
    {
        $transformer = $transformer ?: $this->fetchDefaultTransformer($data);
        return Fractal::item($data, $transformer, config('fractal.collection_key'))->getArray();
    }

    /**
     * 转换记录集合
     *
     * @param $data  Eloquent 数据集
     * @param TransformerAbstract|null $transformer  $transformer 自定义的 transformer 类，为 null 时，则自动获取模型相关的 transformer 类
     * @return mixed  经过 transformer 转换过的数据集
     * @throws \Exception
     */
    public function collection($data, TransformerAbstract $transformer = null)
    {
        $transformer = $transformer ?: $this->fetchDefaultTransformer($data);
        return Fractal::collection($data, $transformer, config('fractal.collection_key'))->getArray();
    }

    /**
     * 获取模型对应的 transformer 实例对象
     *
     * @param $collection  模型对象
     * @return BaseTransformer  模型对象为空时则返回 BaseTransformer
     * @throws \Exception
     */
     protected function fetchDefaultTransformer($collection)
     {
        if (($collection instanceof LengthAwarePaginator || $collection instanceof Collection) && $collection->isEmpty()) {
            return new BaseTransformer();
        }
        $className = $this->getClassName($collection);
        $transformer = $this->getDefaultTransformer($className);  // 从映射表中取出对应的 transformer

        // 没有映射表时
        if (empty($transformer)) {
            $transformer = str_replace('Models', 'Transformers', $className) . 'Transformer';
            if(!class_exists($transformer)){
                throw new \Exception('No transformer for ' . $className);
            }
        }

         return new $transformer;
     }

    /**
     * 从 fractal 配置文件中获取类名和 transformer 映射名，eg： [$className => $classNameTransformer]
     *
     * @param string $className
     * @return \Illuminate\Config\Repository|mixed
     */
    protected function getDefaultTransformer(string $className)
    {
        return config('fractal.transformers.' . $className);
    }

    /**
     * 获取对象名称
     *
     * @param object $object  对象
     * @return string  对象名称 eg：App\Models\Admin\Setting\Log
     */
     protected function getClassName(object $object): string
     {
         if ($object instanceof LengthAwarePaginator || $object instanceof Collection)  {
             return get_class(\Arr::first($object));
         }

         return get_class($object);
     }

}
