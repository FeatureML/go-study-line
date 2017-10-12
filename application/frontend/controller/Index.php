<?php

/**
 * Created by PhpStorm.
 * User: tinywan
 * Date: 2017/6/24
 * Time: 22:33
 */

namespace app\frontend\controller;

use app\common\controller\BaseFrontend;
use app\common\model\Comment;
use think\Cache;
use think\Db;

class Index extends BaseFrontend
{
    // 缓存开关
    protected $cache;
    private $article_cache;
    /**
     * 评论实例
     * @var
     */
    protected $comment_db;

    /**
     * 初始化
     */
    public function _initialize()
    {
        parent::_initialize();
        $this->comment_db = new Comment();
    }

    /**
     * 1、首页列表
     * 2、添加缓存
     * @return mixed
     */
    public function index()
    {
        $article = Db::table("resty_article")
            ->alias('a')
            ->join('resty_category c', 'c.id = a.cate_id')
            ->join('resty_user u', 'u.id = a.author_id')
            ->field("a.title,a.create_time,a.content,a.id,a.views,a.image_thumb,a.desc,c.name as c_name,u.username")
            ->order("a.create_time desc,a.id desc")
            ->paginate(4);
        $comments = Db::table("resty_comment")
            ->alias('c')
            ->join('resty_open_user ou', 'c.user_id = ou.id')
            ->join('resty_article a', 'a.id = c.post_id')
            ->field('a.title,c.comment_id,c.user_id,c.post_id,c.parent_id,c.comment_content,c.parent_id,c.create_time,ou.account,ou.avatar')
            ->order('c.create_time desc')
            ->limit(8)
            ->select();
        $this->assign('comments', $comments);
        $this->assign('list', $article);
        return $this->fetch();
    }

    /**
     * 通过标签查询文章
     */
    public function articleByTag()
    {
        $tagId = input("param.id");
        $article = Db::table("resty_article")
            ->alias('a')
            ->join('resty_article_tag at', 'a.id = at.article_id')
            ->where('at.tag_id', $tagId)
            ->cache(true, 60)
            ->select();
        halt($article);
    }

    /**
     * 根据标签Id查询文章 searchByTagId
     */
    public function searchByTagId()
    {
        $tagId = input("param.id");
        $articles = Db::table("resty_article")
            ->alias('a')
            ->join('resty_article_tag at', 'a.id = at.article_id')
            ->join('resty_category c', 'c.id = a.cate_id')
            ->join('resty_user u', 'u.id = a.author_id')
            ->field("a.title,a.create_time,a.update_time,a.id,a.views,c.name as c_name,u.username")
            ->where('at.tag_id', $tagId)
            ->order("a.create_time desc , a.id desc")
            ->paginate(6);
        $this->assign("articles", $articles);
        return $this->fetch("search");
    }

    /**
     * 根据分类Id查询文章
     */
    public function searchByCategoryId()
    {
        $catId = input("param.id");
        $articles = Db::table("resty_article")
            ->alias('a')
            ->join('resty_article_tag at', 'a.id = at.article_id')
            ->join('resty_category c', 'c.id = a.cate_id')
            ->join('resty_user u', 'u.id = a.author_id')
            ->field("a.title,a.create_time,a.update_time,a.id,a.views,c.name as c_name,u.username")
            ->where('c.id', $catId)
            ->order("a.create_time desc , a.id desc")
            ->paginate(6);
        $this->assign("articles", $articles);
        return $this->fetch("search");
    }

    /**
     * 1、文章详细
     * 2、文章详细单条缓存
     * 3、文章标签多条缓存
     */
    public function detail()
    {
        $this->article_cache = true;
        $postId = input("param.id");
        if (empty($postId) || !is_numeric($postId)) {
            return json(['code' => 404]);
        }
        // 文章单个缓存
        $articleDetailCacheKey = 'resty_article_detail' . $postId;
        if ($this->article_cache == true && Cache::has($articleDetailCacheKey)) {
            $article = Cache::get($articleDetailCacheKey);
        } else {
            $article = Db::table("resty_article")
                ->alias('a')
                ->join('resty_category c', 'c.id = a.cate_id')
                ->join('resty_user u', 'u.id = a.author_id')
                ->field("a.title,a.id,a.create_time,a.content,a.views,c.name as c_name,u.username")
                ->where('a.id', $postId)
                ->find();
            $article['DataSources'] = 'content from Cache';
            // 缓存
            if ($this->article_cache == true) Cache::set($articleDetailCacheKey, $article, 3);
            $article['DataSources'] = 'content from MySQL';
        }

        // 标签多条缓存
        $articleTags = 'resty_tag_detail' . $postId;
        if ($this->article_cache == true && Cache::has($articleTags)) {
            $tags = Cache::get($articleTags);
        } else {
            $tags = Db::table("resty_tag")
                ->alias('t')
                ->join("resty_article_tag at", "at.tag_id = t.id")
                ->where("at.article_id", $postId)
                ->select();
            // 多级缓存
            $tmpTags = [];
            foreach ($tags as $v) {
                $tmpTags[] = [
                    'id' => $v['id'],
                    'name' => $v['name'],
                    'article_id' => $v['article_id'],
                    'tag_id' => $v['tag_id'],
                    'DataSources' => 'tags content from Cache',
                ];
            }
            if ($this->article_cache == true) Cache::set($articleTags, $tmpTags, 3);
        }
        $commentInfos = $this->getCommentListByPostId($postId);
        //halt($commentInfos);
        Db::table('resty_article')->where('id', $postId)->setInc('views');
        $this->assign('article', $article);
        $this->assign('tags', $tags);
        $this->assign('comments', $commentInfos);
        $this->assign('commentCounts', count($commentInfos));
        return $this->fetch();
    }

    /**
     * 评论暂时不做缓存
     * 1、通过文章ID遍历获取全部评论以及回复
     * 2、这里不可以使用缓存，TP5自带的
     * @param $post_id
     * @param int $parent_id
     * @param array $result
     * @return array
     */
    public function getCommentListByPostId($post_id, $parent_id = 0, &$result = [])
    {
        $arr = Db::table("resty_comment")
            ->alias('c')
            ->join('resty_open_user ou', 'c.user_id = ou.id')
            ->field('c.comment_id,c.user_id,c.post_id,c.parent_id,c.comment_content,c.parent_id,c.create_time,ou.account,ou.avatar')
            ->where('c.post_id', $post_id)
            ->where('c.parent_id', $parent_id)
            ->order('c.create_time desc')
            ->select();
        if (empty($arr)) return [];
        foreach ($arr as $cm) {
            $thisArr =& $result[];
            $cm["children"] = $this->getCommentListByPostId($cm["post_id"], $cm["comment_id"], $thisArr);
            $thisArr = $cm;
        }
        return $result;
    }

    /**
     * 由于异步暂时不做缓存
     * 发表评论、回复公用一个控制器
     */
    public function commentStore()
    {
        if (request()->isPost()) {
            $data['post_id'] = input('post.post_id');
            $data['parent_id'] = input('post.parent_id');
            $data['user_id'] = input('post.user_id');
            $data['comment_content'] = input('post.comment_content');
            if (empty($data['comment_content'])) {
                $res = ["code" => 500, "msg" => 'param is error'];
                return json($res);
            }
            $res = $this->comment_db->store($data);
            if ($res["valid"]) {
                /**
                 * 这里要返回的信息应该是新插入的数据显示哦
                 */
                $responseData = Db::table("resty_comment")
                    ->alias('c')
                    ->join('resty_open_user ou', 'c.user_id = ou.id')
                    ->field('c.comment_id,c.user_id,c.post_id,c.parent_id,c.comment_content,c.parent_id,c.create_time,ou.account,ou.avatar')
                    ->where('c.comment_id', $res["id"])
                    ->find();
                $responseData['num'] = count($this->getCommentListByPostId($data['post_id']));
                //格式化时间输出
                $responseData['create_time'] = date('Y-m-d H:i:s', $responseData['create_time']);
                $res = [
                    "code" => 200,
                    "msg" => $res["valid"],
                    'list' => $responseData
                ];
            } else {
                $res = ["code" => 500, "msg" => $res["valid"]];
            }
            return json($res);
        }
    }

    public function commenttest()
    {
        //临时关闭当前模板的布局功能
        $this->view->engine->layout(false);
        $userInfo = Db::table('resty_open_user')->where('id', session('open_user_id'))->find();
        $this->assign('userInfo', $userInfo);
        return $this->fetch();
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
                    $bucket = config('aliyun_oss.bucket');
                    $object = 'uploads/' . $info->getSaveName();
                    $file = './' . $object;  //这个才是文件在本地的真实路径，也是就是你要上传的文件信息
                    $oss = OssInstance::Instance();
                    try {
                        $res = $oss->uploadFile($bucket, $object, $file);
                        if ($res['info']['http_code'] == 200) {
                            // 返回数据
                            $url = "http://tinywan-develop.oss-cn-hangzhou.aliyuncs.com" . DS . $object;
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

    public function info($id)
    {
        $data['post_id'] = 99;
        $data['parent_id'] = 0;
        $data['user_id'] = 12;
        $data['comment_content'] = "Comment content" . rand(00000, 9999);
        $res = $this->comment_db->store($data);
        halt($res);
    }

    /**
     * AutoInstall
     */
    public function autoInstall()
    {
//        $this->view->engine->layout(false);
        return $this->fetch();
    }
}