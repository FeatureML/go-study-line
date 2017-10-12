<?php

/** .-------------------------------------------------------------------
 * |  Github: https://github.com/Tinywan
 * |  Blog: http://www.cnblogs.com/Tinywan
 * |-------------------------------------------------------------------
 * |  Author: Tinywan(ShaoBo Wan)
 * |  DateTime: 2017/8/26 14:42
 * |  Mail: Overcome.wan@Gmail.com
 * '-------------------------------------------------------------------*/

namespace app\backend\controller;

use aliyun\oss\OssInstance;
use app\common\controller\BaseBackend;
use houdunwang\arr\Arr;
use OSS\Core\OssException;
use think\Image;
use think\Log;

class Article extends BaseBackend
{
    protected $db;

    public function _initialize()
    {
        parent::_initialize();
        $this->db = new \app\common\model\Article();
    }

    /**
     * 一个标签可以使多个标签
     * @return mixed
     */
    public function index()
    {
        $articles = $this->db->getAll();
        $categorys = Arr::tree(db('category')->order('id desc')->select(), 'name', $fieldPri = 'id', $fieldPid = 'pid');
        $this->assign('articles', $articles);
        $this->assign('categorys', $categorys);
        $this->assign('tags', db('tag')->where('deleted',0)->select());
        return $this->fetch();
    }

    public function oss()
    {
        $bucket = config('aliyun_oss.BUCKET');
        $object = 'uploads/20170904/29fb780a66238b5fe04e397b1c7dccce.png';
        $file = './' . $object;  //这个才是文件在本地的真实路径，也是就是你要上传的文件信息
        $res = unlink($object);
        halt($res);
        $oss = OssInstance::Instance();
        try {
            $res = $oss->uploadFile($bucket, $object, $file);
            if ($res['info']['http_code'] == 200) {
                rmdir('/' . $object);
            }
        } catch (OssException $e) {
            var_dump($e->getMessage());
        }
    }

    /**
     * 添加文章
     * @return \think\response\Json
     */
    public function store()
    {
        if (request()->isPost()) {
            //图片上传
            if ($_FILES['thumb']['tmp_name']) {
                $data = input('post.');
                $file = request()->file("thumb");
                //开始一个缩略图，直接获取当前请求中的文件上传对象
                $image = Image::open($file);
                // 移动到框架应用根目录/public/uploads/ 目录下
                $info = $file->rule("uniqid")->move(ROOT_PATH . 'public' . DS . 'uploads/article');
                if ($info) {
                    // oss upload
                    $oss = OssInstance::Instance();
                    $bucket = config('aliyun_oss.BUCKET');
                    $thumbName = 'thumb_' . $info->getFilename();
                    //获取文件名
                    $data['thumb'] = $thumbName;

                    /**
                     * 注意：这里定义的$ossbObject 目录路径为OSS 上存储的路径信息,可以自定义的，最好在配置文件中配置最好了
                     */
                    $ossbObject = 'uploads/article';
                    //  定义缩略图保存路径
                    $thumbObjectPath = ROOT_PATH . 'public' . DS . 'uploads/article' . DS . $thumbName;
                    // 按照原图的比例生成一个最大为150*150的缩略图并保存为thumb.png
                    $image->thumb(240, 150)->save($thumbObjectPath);
                    $localDirectory = $ossbObject . DS;
                    /**
                     * 给缩略图左上角添加水印并保存
                     */
                    $logoPath = ROOT_PATH . 'public' . DS . 'uploads/water_logo.png';
                    $image->water($logoPath,\think\Image::WATER_SOUTHEAST,50)->save($thumbObjectPath);
                    /**
                     * 缩略图上传、原始图上传
                     */
                    try {
                        $ossUploadRes = $oss->uploadDir($bucket, $ossbObject, $localDirectory);
                        $data['oss_upload_status'] = 1;
                        $data['image_thumb'] = '/'.$ossbObject.DS.$thumbName;
                        $data['image_origin'] = '/'.$ossbObject.DS.$info->getFilename();
                        // 遍历删除原图和缩略图
                        $this->rmdirs(ROOT_PATH . 'public' . DS . 'uploads/article');
                    } catch (OssException $e) {
                        $data['oss_upload_status'] = json_encode($e->getMessage());
                    }
                }
                $res = $this->db->store($data);
                if ($res["valid"]) {
                    $this->success($res["msg"], "backend/article/index");
                    exit;
                } else {
                    return json(['code' => 500, 'msg' => $res["msg"]]);
                }
            }
            return json(['code' => 500, 'msg' => "Not Forbidden"]);
        }
        return json(['code' => 401, 'msg' => "Not Forbidden"]);
    }

    /**
     * 百度编辑器
     */
    public function saveInfo()
    {
        $ueditor_config = json_decode(preg_replace("/\/\*[\s\S]+?\*\//", "", file_get_contents(ROOT_PATH . 'public' . DS . "common/plugins/ueditor/php/config.json")), true);
        $action = $_GET['action'];
        switch ($action) {
            case 'config':
                $result = json_encode($ueditor_config);
                break;
            /* 上传图片 */
            case 'uploadimage':
                /* 上传涂鸦 */
            case 'uploadscrawl':
                /* 上传视频 */
            case 'uploadvideo':
                /* 上传文件 */
            case 'uploadfile':
                $file = request()->file("upfile");
                // 移动到框架应用根目录/public/uploads/ 目录下
                $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
                if ($info) {
                    //获取文件名
                    $data['thumb'] = "/uploads/" . $info->getSaveName();
                    // oss upload
                    $bucket = config('aliyun_oss.BUCKET');
                    $object = 'uploads/' . $info->getSaveName();
                    $file = './' . $object;  //这个才是文件在本地的真实路径，也是就是你要上传的文件信息
                    $oss = OssInstance::Instance();
                    try {
                        $res = $oss->uploadFile($bucket, $object, $file);
                        if ($res['info']['http_code'] == 200) {
                            // 返回数据
                            $url = config('aliyun_oss.DOMAIN'). $object;
                            Log::info("url == " . $url);
                            $result = json_encode(array(
                                'url' => $url,
                                'title' => htmlspecialchars("11111111111", ENT_QUOTES),
                                'original' => $url,
                                'state' => 'SUCCESS'
                            ));
                            if (!is_dir($object)) unlink($object);
                        }
                    } catch (OssException $e) {
                        $url = "http://" . $_SERVER["HTTP_HOST"] . "/uploads/" . $info->getSaveName();
                        Log::info("url == " . $url);
                        $result = json_encode(array(
                            'url' => $url,
                            'title' => htmlspecialchars("2222222222", ENT_QUOTES),
                            'original' => $url,
                            'state' => 'FAIL'
                        ));
                    }

                } else {
                    $result = json_encode(array(
                        'state' => $file->getError(),
                    ));
                }
                break;
            default:
                $result = json_encode(array(
                    'state' => '请求地址出错'
                ));
                break;
        }
        /* 输出结果 */
        if (isset($_GET["callback"])) {
            if (preg_match("/^[\w_]+$/", $_GET["callback"])) {
                echo htmlspecialchars($_GET["callback"]) . '(' . $result . ')';
            } else {
                echo json_encode(array(
                    'state' => 'callback参数不合法'
                ));
            }
        } else {
            echo $result;
        }
    }

    /**
     * 百度编辑器
     */
    public
    function saveInfo22()
    {
        $ueditor_config = json_decode(preg_replace("/\/\*[\s\S]+?\*\//", "", file_get_contents(ROOT_PATH . 'public' . DS . "common/plugins/ueditor/php/config.json")), true);
        $action = $_GET['action'];
        switch ($action) {
            case 'config':
                $result = json_encode($ueditor_config);
                break;
            /* 上传图片 */
            case 'uploadimage':
                /* 上传涂鸦 */
            case 'uploadscrawl':
                /* 上传视频 */
            case 'uploadvideo':
                /* 上传文件 */
            case 'uploadfile':
                $file = request()->file("upfile");
                // 移动到框架应用根目录/public/uploads/ 目录下
                $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
                if ($info) {
                    //获取文件名
                    $data['thumb'] = "/uploads/" . $info->getSaveName();
                    // oss upload
                    $bucket = config('aliyun_oss.BUCKET');
                    $object = 'uploads/' . $info->getSaveName();
                    $file = './' . $object;  //这个才是文件在本地的真实路径，也是就是你要上传的文件信息
                    $oss = OssInstance::Instance();
                    try {
                        $res = $oss->uploadFile($bucket, $object, $file);
                        if ($res['info']['http_code'] == 200) {
                            // 返回数据
                            $url = config('aliyun_oss.DOMAIN') . $object;
                            Log::info("url == " . $url);
                            $result = json_encode(array(
                                'url' => $url,
                                'title' => htmlspecialchars("11111111111", ENT_QUOTES),
                                'original' => $url,
                                'state' => 'SUCCESS'
                            ));
                            if (!is_dir($object)) unlink($object);
                        }
                    } catch (OssException $e) {
                        $url = "http://" . $_SERVER["HTTP_HOST"] . "/uploads/" . $info->getSaveName();
                        Log::info("url == " . $url);
                        $result = json_encode(array(
                            'url' => $url,
                            'title' => htmlspecialchars("2222222222", ENT_QUOTES),
                            'original' => $url,
                            'state' => 'FAIL'
                        ));
                    }

                } else {
                    $result = json_encode(array(
                        'state' => $file->getError(),
                    ));
                }
                break;
            default:
                $result = json_encode(array(
                    'state' => '请求地址出错'
                ));
                break;
        }
        /* 输出结果 */
        if (isset($_GET["callback"])) {
            if (preg_match("/^[\w_]+$/", $_GET["callback"])) {
                echo htmlspecialchars($_GET["callback"]) . '(' . $result . ')';
            } else {
                echo json_encode(array(
                    'state' => 'callback参数不合法'
                ));
            }
        } else {
            echo $result;
        }
    }

    /**
     * 百度编辑器
     */
    public
    function saveInfo2()
    {
        $ueditor_config = json_decode(preg_replace("/\/\*[\s\S]+?\*\//", "", file_get_contents(ROOT_PATH . 'public' . DS . "common/plugins/ueditor/php/config.json")), true);
        $action = $_GET['action'];
        switch ($action) {
            case 'config':
                $result = json_encode($ueditor_config);
                break;
            /* 上传图片 */
            case 'uploadimage':
                /* 上传涂鸦 */
            case 'uploadscrawl':
                /* 上传视频 */
            case 'uploadvideo':
                /* 上传文件 */
            case 'uploadfile':
                $file = request()->file("upfile");
                // 移动到框架应用根目录/public/uploads/ 目录下
                $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
                if ($info) {
                    $url = "http://" . $_SERVER["HTTP_HOST"] . "/uploads/" . $info->getSaveName();
                    Log::info("url == " . $url);
                    $result = json_encode(array(
                        'url' => $url,
                        'title' => htmlspecialchars("2222222222", ENT_QUOTES),
                        'original' => $info->getFilename(),
                        'state' => 'SUCCESS'
                    ));
                } else {
                    $result = json_encode(array(
                        'state' => $file->getError(),
                    ));
                }
                break;
            default:
                $result = json_encode(array(
                    'state' => '请求地址出错'
                ));
                break;
        }
        /* 输出结果 */
        if (isset($_GET["callback"])) {
            if (preg_match("/^[\w_]+$/", $_GET["callback"])) {
                echo htmlspecialchars($_GET["callback"]) . '(' . $result . ')';
            } else {
                echo json_encode(array(
                    'state' => 'callback参数不合法'
                ));
            }
        } else {
            echo $result;
        }
    }

    /**
     * @return mixed
     */
    public
    function indexTest()
    {
        $this->view->engine->layout(false);
        return $this->fetch();
    }

    public
    function uedit()
    {
        date_default_timezone_set("Asia/chongqing");
        error_reporting(E_ERROR);
        header("Content-Type: text/html; charset=utf-8");
        $CONFIG = json_decode(preg_replace("/\/\*[\s\S]+?\*\//", "", file_get_contents(CONF_PATH . "config.json")), true);
        $action = $_GET['action'];

        switch ($action) {
            case 'config':
                $result = json_encode($CONFIG);
                break;

            /* 上传图片 */
            case 'uploadimage':
                /* 上传涂鸦 */
            case 'uploadscrawl':
                /* 上传视频 */
            case 'uploadvideo':
                /* 上传文件 */
            case 'uploadfile':
                $result = include("action_upload.php");
                break;

            /* 列出图片 */
            case 'listimage':
                $result = include("action_list.php");
                break;
            /* 列出文件 */
            case 'listfile':
                $result = include("action_list.php");
                break;

            /* 抓取远程文件 */
            case 'catchimage':
                $result = include("action_crawler.php");
                break;

            default:
                $result = json_encode(array(
                    'state' => '请求地址出错'
                ));
                break;
        }

        /* 输出结果 */
        if (isset($_GET["callback"])) {
            if (preg_match("/^[\w_]+$/", $_GET["callback"])) {
                echo htmlspecialchars($_GET["callback"]) . '(' . $result . ')';
            } else {
                echo json_encode(array(
                    'state' => 'callback参数不合法'
                ));
            }
        } else {
            echo $result;
        }
    }
}
