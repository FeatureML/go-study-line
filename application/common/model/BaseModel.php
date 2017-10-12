<?php
/** .-------------------------------------------------------------------
 * |  Github: https://github.com/Tinywan
 * |  Blog: http://www.cnblogs.com/Tinywan
 * |-------------------------------------------------------------------
 * |  Author: Tinywan(ShaoBo Wan)
 * |  DateTime: 2017/9/9 12:29
 * |  Mail: Overcome.wan@Gmail.com
 * |  Functiin ： Model 基类
 * '-------------------------------------------------------------------*/

namespace app\common\model;

use Faker\Factory;
use think\Model;
use think\Request;

class BaseModel extends Model
{
    /**
     * 单实例
     * @return Request
     */
    protected static function request()
    {
        return Request::instance();
    }

    /**
     * 初始化日志操作
     */
    protected static function init()
    {
        /**
         * [1] 插入之前操作
         * $model 当前对象，如：在调用Commnet 插入数据的时候返回的对象：Commentapp\common\model\Comment Object
         */
        self::beforeInsert(function ($model) {
            //获取执行的model名称 如：$base_name = Comment
            $base_name = basename(str_replace('\\', '/', get_called_class()), '.php');
            $request = Request::instance();
            $module = $request->module();
            $controller = $request->controller();
            $action = $request->action();
            $data["guid"] = session('open_user_id');
            $data['event_type'] = "after_insert";
            $data['url'] = $request->url();
            $data['module'] = $module;
            $data['controller'] = $controller;
            $data['action'] = $action;
            $data['model'] = $base_name;
            $data["query_string"] = json_encode($request->param());
            $data["ipaddr"] = $request->ip();
            $data["addtime"] = date('Y-m-d H:i:s');
            $model->table('resty_logs')->insert($data);
        });
    }

    /**
     * 获取随机姓名
     * @return string
     * @static
     */
    public static function getRandUserName()
    {
        $faker = Factory::create($locale = 'zh_CN');
        return $faker->name;
    }

}