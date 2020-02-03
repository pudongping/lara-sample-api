<?php
/**
 * 初始化请求参数
 *
 * Created by PhpStorm.
 * User: Alex
 * Date: 2020/2/2
 * Time: 19:49
 */

namespace App\Http\Middleware;

use Closure;
use App\Support\TempValue;
use Auth;
use DB;
use App;

class InitParams
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // 当前已经登陆的用户
        TempValue::$currentUser = Auth::user();
        // 是否开启 debug
        TempValue::$debug = $debug = (boolean)$request->input('debug', 0);
        // 当前 http 请求方式
        TempValue::$httpMethod = strtolower($request->getMethod());
        // 当前控制器名称
        TempValue::$controller = getCurrentAction()['controller'];
        // 当前方法名称
        TempValue::$action = getCurrentAction()['method'];

        // 是否分页，默认不分页
        TempValue::$nopage = (boolean)$request->input('nopage', 1);
        // 当前页码，默认为第一页
        TempValue::$page = (int)$request->input('page', 1);
        // 分页显示数量
        TempValue::$perPage = (int)$request->input('per_page');
        /**
         * 排序规则
         * 支持 TempValue::$orderBy = id,desc|name,asc
         * 支持 $sortColumn = ['id','name'] , $sort = ['desc','asc']
         */
        TempValue::$orderBy = $request->input('order_by');

        if (config('app.debug') && $debug) {
            // 在连接上启用查询日志
            DB::enableQueryLog();
            $connections = array_keys(config('database.connections', []));
            foreach ($connections as $connection){
                // 循环开启每一种数据库的查询日志
                DB::connection($connection)->enableQueryLog();
            }
        }

        return $next($request);
    }


}
