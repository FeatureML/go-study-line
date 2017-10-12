<?php
/**
 * 微信开发文档：http://www.cnblogs.com/txw1958/p/weixin-js-sdk-php-demo.html
 */

namespace app\wechat\controller;

use EasyWeChat\Foundation\Application;
use think\Controller;
use think\Request;

class Index extends Controller
{
    /**
     * scope=snsapi_base 实例
     * 这里采用高级授权模式，为了获取用户信息
     * 这个页面是用户进来就能够刚问的页面，也就是首次进来的页面
     * 首先访问的地址 ；http://wanwecaht.amai8.com/wechat/Index/index
     */
    public function index()
    {
        //$scope = "snsapi_base";
        $scope = "snsapi_userinfo";
        $appid = 'wx94c43716d8a91f3f';
        /*基本授权 方法跳转地址*/
        $redirect_uri = urlencode('http://www.tinywan.com/wechat/Index/getUserInfo');
        $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . $appid . "&redirect_uri=" . $redirect_uri . "&response_type=code&scope=${scope}&state=1234#wechat_redirect";
        header('location:' . $url);
    }
    /**
         * 拉取用户信息
     */
    public function getUserInfo()
    {
        $appid = 'wx94c43716d8a91f3f';
        $appsecret = 'd4624c36b6795d1d99dcf0547af5443d';
        /*回调的时候自带的这个参数*/
        $request = Request::instance();
        $code = $request->get('code');

        //第一步:取全局access_token
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&secret=$appsecret";
        $token = getJson($url);

        //第二步:取得openid
        $oauth2Url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$appid&secret=$appsecret&code=$code&grant_type=authorization_code";
        $oauth2 = getJson($oauth2Url);
        //第三步:根据全局access_token和openid查询用户信息
        $access_token = $token["access_token"];
        $openid = $oauth2['openid'];
        $get_user_info_url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=$access_token&openid=$openid&lang=zh_CN";
        $userinfo = getJson($get_user_info_url);
        //打印用户信息
        halt($userinfo);
    }

    /**
     * 微信分享
     * @return \think\response\View
     */
    public function share()
    {
        $jsapiTicket = $this->getJsApiTicket();
        $timestamp = time();
        $nonceStr = $this->createNonceStr();
        $url = "http://wanwecaht.amai8.com/wechat/Index/share";
        //signature
        $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";
        $signature = sha1($string);
        $this->assign([
            'name' => '分享到朋友圈',
            'time' => $timestamp,
            'nonceStr' => $nonceStr,
            'signature' => $signature
        ]);
        return $this->fetch();
    }

    /**
     * PHP取微信access_token并全局存储与更新
     * @return null
     */
    private function getAccessToken()
    {
        $appid = "wx94c43716d8a91f3f";
        $appsecret = "d4624c36b6795d1d99dcf0547af5443d";
        $tokenFile = CONF_PATH . "access_token.json";//缓存文件名
        $data = json_decode(file_get_contents($tokenFile));
        if (empty($data)) {
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&secret=$appsecret";
            $res = getJson($url);
            $access_token = $res['access_token'];
            if ($access_token) {
                $data['expire_time'] = time() + 10;
                $data['access_token'] = $access_token;
                file_put_contents($tokenFile, json_encode($data));
            }
        } elseif ($data) {
            if ($data->expire_time < time() or !$data->expire_time) {
                $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&secret=$appsecret";
                $res = getJson($url);
                $access_token = $res['access_token'];
                if ($access_token) {
                    $data->expire_time = time() + 10;
                    $data->access_token = $access_token;
                    file_put_contents($tokenFile, json_encode($data));
                }
            } else {
                $access_token = $data->access_token;
            }
        } else {
            $access_token = null;
        }
        return $access_token;
    }

    /**
     * @param int $length
     * @return string
     */
    private function createNonceStr($length = 16)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    /**
     * jsapi_ticket 应该全局存储与更新，以下代码以写入到文件中做示例
     * @return null
     */
    private function getJsApiTicket()
    {

        $tokenFile = CONF_PATH . "jsapi_ticket.json";//缓存文件名
        $data = json_decode(file_get_contents($tokenFile),true);
        if (empty($data)) {
            $accessToken = $this->getAccessToken();
            $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken";
            $res = json_decode($this->httpGet($url), true);
            $ticket = $res["ticket"];
            if ($ticket) {
                $data['expire_time'] = time() + 7000;
                $data['jsapi_ticket'] = $ticket;
                file_put_contents($tokenFile, json_encode($data));
            }
        } elseif ($data) {
            if ($data["expire_time"] < time() or !$data["expire_time"]) {
                $accessToken = $this->getAccessToken();
                $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken";
                $res = json_decode($this->httpGet($url), true);
                $ticket = $res["ticket"];
                if ($ticket) {
                    $data['expire_time'] = time() + 7100;
                    $data['jsapi_ticket'] = $ticket;
                    file_put_contents($tokenFile, json_encode($data));
                }
            } else {
                $ticket = $data["jsapi_ticket"];
            }
        } else {
            $ticket = null;
        }
        return $ticket;
    }

    /**
     * @param $url
     * @return mixed
     */
    private function httpGet($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        curl_setopt($curl, CURLOPT_URL, $url);

        $res = curl_exec($curl);
        curl_close($curl);

        return $res;
    }


}
