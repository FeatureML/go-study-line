<?php
/** .-------------------------------------------------------------------
 * |  Github: https://github.com/Tinywan
 * |  Blog: http://www.cnblogs.com/Tinywan
 * |-------------------------------------------------------------------
 * |  Author: Tinywan(SHaoBo Wan)
 * |  DateTime: 2017/9/21 10:32
 * |  Mail: Overcome.wan@Gmail.com
 * |  Created by PhpStorm.
 * '-------------------------------------------------------------------*/

namespace app\backend\controller;

use \aliyun\oss\OssInstance;
use app\common\controller\BaseBackend;
use houdunwang\arr\Arr;
use OSS\Core\OssException;
use think\Db;
use think\Image;
use think\Log;

class Product extends BaseBackend
{
    /**
     * https://www.tinywan.top/backend/Product/index
     * @return string
     */
    public function index()
    {
        var_dump(OssInstance::Instance());
        return __FUNCTION__;
    }

    /**
     * 添加商品
     * https://www.tinywan.top/backend/Product/store
     */
    public function store()
    {
        if (request()->isPost()) {
            $model = Db::table('resty_product')->insertGetId(input('post.'));
            if ($model) {
                $this->success('success');
                exit;
            } else {
                $this->error('fail');
                exit;
            }
        }
        $products = Db::table("resty_product")
            ->alias('p')
            ->join('resty_file f', 'p.file_id = f.id')
            ->join('resty_category c','c.id = p.cid')
            ->field("p.*,f.min_path,f.path,c.name as cName")
            ->order('p.id desc')
            ->select();
        $categorys = Arr::tree(db('category')->order('id desc')->select(), 'name', $fieldPri = 'id', $fieldPid = 'pid');
        $this->assign('categorys', $categorys);
        $this->assign('products', $products);
        return $this->fetch('index');
    }


    /**
     *  商品图片上传
     *  Help：https://www.hongxuelin.com/php/173.html
     * @return \think\response\Json
     */
    public function productUploadImage()
    {
        if (request()->isPost()) {
            $file = request()->file("file");
            if ($file) {
                $info = $file->rule("uniqid")->move(ROOT_PATH . 'public' . DS . 'uploads/products');
                if ($info) {
                    // oss upload
                    $oss = OssInstance::Instance();
                    $bucket = config('aliyun_oss.BUCKET');
                    $thumbName = 'thumb_' . $info->getFilename();
                    Log::error('-------------111111111---------'.$thumbName);
                    //获取文件名
                    $ossbObject = 'uploads/products';
                    //  定义缩略图保存路径
                    $thumbObjectPath = ROOT_PATH . 'public' . DS . 'uploads/products' . DS . $thumbName;
                    Log::error('------------22222----------'.$thumbObjectPath);
                    // 按照原图的比例生成一个最大为150*150的缩略图并保存为thumb.png
//                    $image = Image::open($file);
//                    $image->thumb(240, 150)->save($thumbObjectPath);
                    $localDirectory = $ossbObject . DS;
                    Log::error('------------3333333333----------'.$localDirectory);
                    try {
                        $oss->uploadDir($bucket, $ossbObject, $localDirectory);
                        $data['oss_upload_status'] = 1;
                        $data['min_path'] = '/'.$ossbObject.DS.$thumbName;
                        $data['path'] = '/'.$ossbObject.DS.$info->getFilename();
                        // 遍历删除原图和缩略图
                        $this->rmdirs(ROOT_PATH . 'public' . DS . 'uploads/products');
                        Log::error('------------4444444444----------'.$localDirectory);
                    } catch (OssException $e) {
                        Log::error('------------5555555555555----------'.$localDirectory);
                        $data['oss_upload_status'] = json_encode($e->getMessage());
                    }
                    $data['pid'] = 120;
                    $insertId = Db::table('resty_file')->insertGetId($data);
                    $res = [
                        'code' => 200,
                        'msg' => 'success',
                        'fileId' => $insertId,
                        'path' => config('aliyun_oss.DOMAIN').$ossbObject.DS.$info->getFilename()
                    ];
                } else {
                    $res = [
                        'code' => 500,
                        'msg' => $file->getError()
                    ];
                }
                return json($res);
            }
            return json(['code' => 500, 'msg' => "upload file name error"]);
        }
        return json(['code' => 401, 'msg' => "Not Forbidden"]);
    }

    /**
     * 删除产品的同时删除掉对应的图片
     * 【1】开启事务
     * 【2】查看该物品是否存在
     * 【3】删除商品记录
     * 【3】删除商品图片【物理删除】,如果有空目录会一并删除
     * 【4】删除图片记录信息表
     * 【5】判断物品删除和图片删除是否都删除成功 【TURE 提交事务】【FALE 回滚事务】
     * 【6】JSON返回Client
     */
    public function delProduct()
    {
        $id = I('post.id');
        $model = M('Product');
        // 开启事务
        $model->startTrans();
        //$where['id'] = ':id';
        $find = $model->where(['id' => ':id'])->bind(':id', $id, \PDO::PARAM_INT)->find();
        if ($find == false) {
            $response = ['errcode' => 500, 'errmsg' => 'Product is not exists', 'dataList' => $find];
            $this->ajaxReturn($response, 'JSON');
        }
        $result = $model->where(['id' => ':id'])->bind(':id', $id, \PDO::PARAM_INT)->delete();
        if ($result == false) {
            $response = ['errcode' => 500, 'errmsg' => 'Product delete fail', 'dataList' => $result];
            $this->ajaxReturn($response, 'JSON');
        }
        // 遍历所有产品的图片，进行物理删除
        $where2['pid'] = ':pid';
        $thumbs = M('File')->where($where2)->bind(':pid', $id, \PDO::PARAM_INT)->select();

        if ($thumbs && is_array($thumbs)) {
            foreach ($thumbs as $thumb) {
                if (file_exists(C('UPLOAD_PATH') . $thumb['path'])) {
                    if (!unlink(C('UPLOAD_PATH') . $thumb['path'])) {
                        $response = ['errcode' => 500, 'errmsg' => 'unlink path fail'];
                        $this->ajaxReturn($response, 'JSON');
                    }
                }
                if (file_exists(C('UPLOAD_PATH') . $thumb['min_path'])) {
                    if (!unlink(C('UPLOAD_PATH') . $thumb['min_path'])) {
                        $response = ['errcode' => 500, 'errmsg' => 'unlink min_path fail'];
                        $this->ajaxReturn($response, 'JSON');
                    }
                }
                //如果目录文件为空，则删除目录文件，也就是个目录下面的最后一个文件一起删除的
                //dirname返回路径的目录部分,
                if (is_dir(dirname(C('UPLOAD_PATH') . $thumb['path']))) {
                    //如果目录文件为空，则删除目录文件
                    rmdir(dirname(C('UPLOAD_PATH') . $thumb['path']));
                    /**
                     * rmdir() 删除空白目录
                     * 注意：这里删除的对应的网站根目录。和网站域名是没有关系的,也就是完整路径哦
                     */
                }
            }
        }

        //同时删除文件记录表中数据file表中的数据
        $result2 = M('File')->where($where2)->bind(':pid', $id, \PDO::PARAM_INT)->delete();
        if ($thumbs && !$result2) {
            $response = ['errcode' => 500, 'errmsg' => 'File fail'];
            $this->ajaxReturn($response, 'JSON');
        }

        if ($thumbs) {
            if ($result && $result2) {
                $model->commit();
                $response = array('errcode' => 200, 'errmsg' => '恭喜你,删除成功!');
                $this->ajaxReturn($response, 'JSON');
            }
        } else {
            if ($result) {
                $model->commit();
                $response = array('errcode' => 200, 'errmsg' => '恭喜你,删除成功!');
                $this->ajaxReturn($response, 'JSON');
            }
        }
        $model->rollback();
        $response = ['errcode' => 500, 'errmsg' => 'File and Prodect delete', 'dataList' => $result];
        $this->ajaxReturn($response, 'JSON');
    }
}