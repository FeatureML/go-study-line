<?php
/**
 * Created by PhpStorm.
 * User: tinywan
 * Date: 2017/6/27
 * Time: 22:01
 */

namespace app\frontend\controller;

use think\Controller;

class Image extends Controller
{
    /**
     *
     */
    public function index()
    {
        return __FUNCTION__;
    }

    /**
     * 裁剪图片
     */
    public function imageCrop()
    {
        $imagePath = ROOT_PATH . 'public' . DS . 'uploads/20170807/92603aa925a01dd7c86c70874d1782c6.png';
        $image =  \think\Image::open($imagePath);
        //将图片裁剪为300x300并保存为crop.png
        $res = $image->crop(300,300)->save(ROOT_PATH . 'public' . DS . 'uploads/crop.png');
        var_dump($res);
    }

    /**
     * 生成缩略图
     */
    public function imageThumb()
    {
        $imagePath = ROOT_PATH . 'public' . DS . 'uploads/20170807/92603aa925a01dd7c86c70874d1782c6.png';
        $image =  \think\Image::open($imagePath);
        // 按照原图的比例生成一个最大为150*150的缩略图并保存为thumb.png
        $res = $image->thumb(150,150)->save(ROOT_PATH . 'public' . DS . 'uploads/thumb.png');
        var_dump($res);
    }

    /**
     * 添加水印
     */
    public function imageWater()
    {
        $imagePath = ROOT_PATH . 'public' . DS . 'uploads/20170807/92603aa925a01dd7c86c70874d1782c6.png';
        $logoPath = ROOT_PATH . 'public' . DS . 'uploads/logo.png';
        $image =  \think\Image::open($imagePath);
        // 给原图左上角添加水印并保存water_image.png
        $res = $image->water($logoPath,\think\Image::WATER_SOUTHWEST,50)->save(ROOT_PATH . 'public' . DS . 'uploads/water_image.png');
        var_dump($res);
    }
}