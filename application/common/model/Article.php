<?php
/** .-------------------------------------------------------------------
 * |  Github: https://github.com/Tinywan
 * |  Blog: http://www.cnblogs.com/Tinywan
 * |-------------------------------------------------------------------
 * |  Author: Tinywan(ShaoBo Wan)
 * |  DateTime: 2017/9/10 16:43
 * |  Mail: Overcome.wan@Gmail.com
 * |  Created by PhpStorm
 * '-------------------------------------------------------------------*/
namespace app\common\model;

use houdunwang\arr\Arr;
use think\Db;

class Article extends BaseModel
{
    protected $pk = "id";
    protected $table = "resty_article"; //完整的表名

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;

    //如果数据库没有创建update_time和更新时间update_time,则按照以下的做一个映射既可以
    protected $createTime  = "create_time";
    protected $updateTime  = "update_time";
    //软删除的映射
    protected $deleteTime = "delete_time";

    //自动完成 字段
    protected $auto = [
        "author_id"
    ];

    //插入的时候自动完成
    protected $insert = [
        "create_time"
    ];

    //更新自动完成
    protected $update = [
        "update_time"
    ];

    //模型事件是指在进行模型的写入操作的时候触发的操作行为，包括模型的save方法和delete方法。

    /**
     * 初始化处理
     */
    protected static function init()
    {

    }

    protected function setAuthorIdAttr()
    {
        return session('admin.admin_id');
    }

    public function getAll(){
        return Db::table("resty_article")
            ->alias('a')
            ->join('__CATEGORY__ c','a.cate_id = c.id')
            ->field("a.title,a.create_time,a.update_time,a.id,c.name as c_name")
            ->order("a.create_time desc,a.id desc")
            ->select();
    }

    /**
     * 添加文章
     * @param $data
     * @return array
     */
    public function store($data)
    {
        // tags 验证
        if(!isset($data['tag'])){
            return ['valid' => 0, 'msg' => "必须选择一个标签"];
        }
        // 过滤post数组中的非数据表字段数据 allowField(true)
        $result = $this->validate(true)->allowField(true)->save($data);
        foreach ($data['tag'] as $v){
            $relData = [
                'article_id'=>$this->id,
                'tag_id'=>$v
            ];
            (new ArticleTag())->save($relData);
        }
        if (false === $result) {
            // 验证失败 输出错误信息
            return ['valid' => 0, 'msg' => $this->getError()];
        }
        return ['valid' => 1, 'msg' => "添加成功"];
    }

    /**
     * 编辑分类
     * @param $data
     * @return array
     */
    public function edit($data)
    {
        // id 已经存在的，只能更新
        $result = $this->validate(true)->save($data, [$this->pk => $data['id']]);
        if (false === $result) {
            return ['valid' => 0, 'msg' => $this->getError()];
        }
        return ['valid' => 1, 'msg' => "编辑成功"];
    }

    /**
     * 获取分类编辑name
     */
    public function getEditCategory($id)
    {
        // 1 首先查找到 $id 的子集
        $subIds = $this->getSubCategory(db('category')->select(), $id);
        // 2 将当前的分类id追加进去
        $subIds[] = $id;
        // 3 找到除了自己和自己子集的所有数据,构成梳妆
        $res = db('category')->whereNotIn('id', $subIds)->select();
        return Arr::tree($res, 'name', $fieldPri = 'id', $fieldPid = 'pid');

    }

    public function getSubCategory($allData, $id)
    {
        static $tmp = [];
        foreach ($allData as $k => $v) {
            if ($id == $v['pid']) {
                $tmp[] = $v['id'];
                $this->getSubCategory($allData, $v['id']);
            }
        }
        return $tmp;
    }

    /**
     * 删除
     * @param $id
     * @return array
     */
    public function del($id)
    {
        //1 获取当前删除数据id 的pid的值
        $pid = $this->where('id', $id)->value('pid');
        //2 将当前要删除的id的子集数据的pid 修改为删除数据自己的pid ,这样子就做到了往上提一级的概念
        $this->where('pid', $id)->update(['pid' => $pid]);
        //3 执行当前数据的删除
        $res = Category::destroy($id);
        if (false === $res) return ['valid' => 0, 'msg' => "删除失败"];
        return ['valid' => 1, 'msg' => "删除成功"];
    }
}
