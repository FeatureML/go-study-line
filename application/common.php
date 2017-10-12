<?php
//--------------------------------------------------common------------------------------------------------------------

/* * *************************
 * 生成随机字符串，可以自己扩展   //若想唯一，只需在开头加上用户id
 * $type可以为：upper(只生成大写字母)，lower(只生成小写字母)，number(只生成数字)
 * $len为长度，定义字符串长度
 * mark 2017/8/15
 * ************************** */
function get_random($type, $len = 0)
{
    $new = '';
    $string = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';  //数据池
    if ($type == 'upper') {
        for ($i = 0; $i < $len; $i++) {
            $new .= $string[mt_rand(36, 61)];
        }
        return $new;
    }
    if ($type == 'lower') {
        for ($i = 0; $i < $len; $i++) {
            $new .= $string[mt_rand(10, 35)];
        }
        return $new;
    }
    if ($type == 'number') {
        for ($i = 0; $i < $len; $i++) {
            $new .= $string[mt_rand(0, 9)];
        }
        return $new;
    }
}

//计算该月有几天
function getdaysInmonth($month, $year)
{
    $days = '';
    if ($month == 1 || $month == 3 || $month == 5 || $month == 7 || $month == 8 || $month == 10 || $month == 12)
        $days = 31;
    else if ($month == 4 || $month == 6 || $month == 9 || $month == 11)
        $days = 30;
    else if ($month == 2) {
        if (isLeapyear($year)) {
            $days = 29;
        } else {
            $days = 28;
        }
    }
    return ($days);
}

//判断是否为润年
function isLeapyear($year)
{
    if ((($year % 4) == 0) && (($year % 100) != 0) || (($year % 400) == 0)) {
        return (true);
    } else {
        return (false);
    }
}

//生成订单15位
function get_auto_order($ord = 0)
{
    //自动生成订单号  传入参数为0 或者1   0为本地  1为线上订单
    if ($ord == 0) {
        $str = '00' . time() . rand(1000, 9999); //00 本地订单
    } else {
        $str = '99' . time() . rand(1000, 9999);  //11 线上订单
    }
    return $str;
}

//生成订单15位
function get_auto_new_order($ord = 0)
{
    srand(time());
    //自动生成订单号  传入参数为0 或者1   0为本地  1为线上订单
    if ($ord == 0) {
        $str = '00' . time() . mt_rand(100000, 999999); //00 本地订单
    } else {
        $str = '11' . time() . mt_rand(100000, 999999);  //11 线上订单
    }
    return $str;
}

/**
 * 判断是PC端还是wap端访问
 * @return type 判断手机移动设备访问
 */
function is_mobile()
{
    // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
    if (isset ($_SERVER['HTTP_X_WAP_PROFILE'])) {
        return true;
    }
    // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
    if (isset ($_SERVER['HTTP_VIA'])) {
        // 找不到为flase,否则为true
        return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
    }
    // 脑残法，判断手机发送的客户端标志,兼容性有待提高
    if (isset ($_SERVER['HTTP_USER_AGENT'])) {
        $clientkeywords = ['nokia',
            'sony',
            'ericsson',
            'mot',
            'samsung',
            'htc',
            'sgh',
            'lg',
            'sharp',
            'sie-',
            'philips',
            'panasonic',
            'alcatel',
            'lenovo',
            'iphone',
            'ipod',
            'blackberry',
            'meizu',
            'android',
            'netfront',
            'symbian',
            'ucweb',
            'windowsce',
            'palm',
            'operamini',
            'operamobi',
            'openwave',
            'nexusone',
            'cldc',
            'midp',
            'wap',
            'mobile'
        ];
        // 从HTTP_USER_AGENT中查找手机浏览器的关键字
        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
            return true;
        }
    }
    // 协议法，因为有可能不准确，放到最后判断
    if (isset ($_SERVER['HTTP_ACCEPT'])) {
        // 如果只支持wml并且不支持html那一定是移动设备
        // 如果支持wml和html但是wml在html之前则是移动设备
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
            return true;
        }
    }
    return false;
}

/**
 * 判断是否为安卓手机
 * @return bool
 */
function is_android()
{
    if (isset($_SERVER['HTTP_USER_AGENT'])) {
        $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
        if (strpos($agent, 'android') !== false)
            return true;
    }
    return false;
}

/**
 * 判断是否为iphone或者ipad
 * @return bool
 */
function is_ios()
{
    if (isset($_SERVER['HTTP_USER_AGENT'])) {
        $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
        if (strpos($agent, 'iphone') || strpos($agent, 'ipad'))
            return true;
    }
    return false;
}

/**
 * 判断是否为微信内置浏览器打开
 * @return bool
 */
function is_wechet()
{
    if (isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
        return true;
    }
    return false;
}

/**
 * 判断当前设备，1：安卓；2：IOS；3：微信；0：未知
 * @return int
 */
function is_device()
{
    if ($_SERVER['HTTP_USER_AGENT']) {
        $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
        if (strpos($agent, 'micromessenger') !== false)
            return 3;
        elseif (strpos($agent, 'iphone') || strpos($agent, 'ipad'))
            return 2;
        else
            return 1;
    }
    return 0;
}

/**
 * 日期转换成几分钟前
 * @param $date
 * @return string
 */
function date_to_time($date)
{
    $timer = strtotime($date);
    $diff = $_SERVER['REQUEST_TIME'] - $timer;
    $day = floor($diff / 86400);
    $free = $diff % 86400;
    if ($day > 0) {
        if (15 < $day && $day < 30) {
            return "半个月前";
        } elseif (30 <= $day && $day < 90) {
            return "1个月前";
        } elseif (90 <= $day && $day < 187) {
            return "3个月前";
        } elseif (187 <= $day && $day < 365) {
            return "半年前";
        } elseif (365 <= $day) {
            return "1年前";
        } else {
            return $day . "天前";
        }
    } else {
        if ($free > 0) {
            $hour = floor($free / 3600);
            $free = $free % 3600;
            if ($hour > 0) {
                return $hour . "小时前";
            } else {
                if ($free > 0) {
                    $min = floor($free / 60);
                    $free = $free % 60;
                    if ($min > 0) {
                        return $min . "分钟前";
                    } else {
                        if ($free > 0) {
                            return $free . "秒前";
                        } else {
                            return '刚刚';
                        }
                    }
                } else {
                    return '刚刚';
                }
            }
        } else {
            return '刚刚';
        }
    }
}

/**
 * 截取长度
 * @param $rawString
 * @param string $length
 * @param string $etc
 * @param bool $isStripTag
 * @return string
 */
function get_sub_string($rawString, $length = '100', $etc = '...', $isStripTag = true)
{
    $rawString = str_replace('_baidu_page_break_tag_', '', $rawString);
    $result = '';
    if ($isStripTag)
        $string = html_entity_decode(trim(strip_tags($rawString)), ENT_QUOTES, 'UTF-8');
    else
        $string = trim($rawString);
    $strlen = strlen($string);
    for ($i = 0; (($i < $strlen) && ($length > 0)); $i++) {
        if ($number = strpos(str_pad(decbin(ord(substr($string, $i, 1))), 8, '0', STR_PAD_LEFT), '0')) {
            if ($length < 1.0) {
                break;
            }
            $result .= substr($string, $i, $number);
            $length -= 1.0;
            $i += $number - 1;
        } else {
            $result .= substr($string, $i, 1);
            $length -= 0.5;
        }
    }
    if ($isStripTag)
        $result = htmlspecialchars($result, ENT_QUOTES, 'UTF-8');

    if ($i < $strlen) {
        $result .= $etc;
    }
    return $result;
}

/**
 * get url 获取json数据
 * @param $url
 * @return mixed
 */
function getJson($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($ch);
    curl_close($ch);
    return json_decode($output, true);
}

function get_uri($url)
{
    $ch = curl_init() or die (curl_error());
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 360);
    $response = curl_exec($ch);
    curl_close($ch);
    //显示获得的数据
    return $response;
}

/**
 * 参数1：
 * 参数2：
 * 参数3：提交的$cookies
 * 参数4：是否返回$cookies
 * @param $url  访问的URL
 * @param string $post post数据(不填则为GET)
 * @param string $cookie
 * @param int $returnCookie
 * @return mixed|string
 */
function curl_request($url, $post = '', $cookie = '', $returnCookie = 0)
{
    // 设置头部信息返回为json 格式
    $headers = ["Accept: application/json"];
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)');
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
    curl_setopt($curl, CURLOPT_REFERER, "http://XXX");
    if ($post) {
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post));
    }
    if ($cookie) {
        curl_setopt($curl, CURLOPT_COOKIE, $cookie);
    }
    curl_setopt($curl, CURLOPT_HEADER, $returnCookie);
    curl_setopt($curl, CURLOPT_TIMEOUT, 10);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    $data = curl_exec($curl);
    if (curl_errno($curl)) {
        return curl_error($curl);
    }
    curl_close($curl);
    if ($returnCookie) {
        list($header, $body) = explode("\r\n\r\n", $data, 2);
        preg_match_all("/Set\-Cookie:([^;]*);/", $header, $matches);
        $info['cookie'] = substr($matches[1][0], 1);
        $info['content'] = $body;
        return $info;
    } else {
        return $data;
    }
}

/**
 * 生成 auth_key
 * @param string $check_str
 * @return string
 */
function get_auth_key($check_str)
{
    if (empty($check_str)) return ['valid' => 0, 'msg' => "参数不合适"];
    // 1 过期时间
    $send_email_expire_time = config('email.EMAIL_SEND_EXPIRE_TIME');
    // 2 私有密钥
    $send_email_private_key = config('email.EMAIL_SEND_PRIVATE_KEY');
    // 3 分钟
    $timestatmp = strtotime(date('Y-m-d H:i:s', strtotime("+" . $send_email_expire_time . "minute")));
    $uuid = 0;
    $uid = 0;
    $hash_value = md5($check_str . '-' . $timestatmp . '-' . $uuid . '-' . $uid . '-' . $send_email_private_key);
    $auth_key = $timestatmp . '-' . $uuid . '-' . $uid . '-' . $hash_value;
    return $auth_key;
}

/**
 * 邮箱地址有效期验证
 * @param string $check_str
 * @param string $auth_key
 * @return array
 */
function check_auth_key($check_str, $auth_key)
{
    if (empty($check_str) && empty($auth_key)) return ['valid' => 0, 'msg' => "参数不合适"];
    $send_email_expire_time = substr($auth_key, 0, 10);
    if ($send_email_expire_time < time()) return ['valid' => 0, 'msg' => "该URL地址已经过期了"];
    $uuid = 0;
    $uid = 0;
    $send_email_private_key = config('email.EMAIL_SEND_PRIVATE_KEY'); // 私有密钥
    $sequest_hash_value = substr($auth_key, -32);
    $hash_value = md5($check_str . '-' . $send_email_expire_time . '-' . $uuid . '-' . $uid . '-' . $send_email_private_key);
    if ($hash_value != $sequest_hash_value) return ['valid' => 0, 'msg' => "URL地址签名错误"];
    return ['valid' => 1, 'msg' => "签名验证通过"];
}

/**
 * 163 发送邮件 本地OK，线上有问题，暂时不用
 * @param  array $address 需要发送的邮箱地址 发送给多个地址需要写成数组形式
 * @param  string $subject 标题
 * @param  string $content 内容
 * @return array  放回状态吗和提示信息
 */
function send_email($address, $subject, $content)
{
    $email_smtp = config('email163.EMAIL_SMTP');
    $email_username = config('email163.EMAIL_USERNAME');
    $email_password = config('email163.EMAIL_PASSWORD');
    $email_from_name = config('email163.EMAIL_FROM_NAME');
    if (empty($email_smtp) || empty($email_username) || empty($email_password) || empty($email_from_name)) {
        return ["error" => 1, "message" => '邮箱请求参数不全，请检测邮箱的合法性'];
    }
    $phpmailer = new PHPMailer();
    // 	设置PHPMailer使用SMTP服务器发送Email
    $phpmailer->IsSMTP();
    // 	设置为html格式
    $phpmailer->IsHTML(true);
    // 	设置邮件的字符编码'
    $phpmailer->CharSet = 'UTF-8';
    // 设置SMTP服务器。
    $phpmailer->Host = $email_smtp;
    // 设置为"需要验证"
    $phpmailer->SMTPAuth = true;
    // 设置用户名
    $phpmailer->Username = $email_username;
    // 设置密码
    $phpmailer->Password = $email_password;
    // 设置邮件头的From字段。
    $phpmailer->From = $email_username;
    // 设置发件人名字
    $phpmailer->FromName = $email_from_name;
    // 添加收件人地址，可以多次使用来添加多个收件人
    if (is_array($address)) {
        foreach ($address as $addressv) {
            //验证邮件地址,非邮箱地址返回为false
            if (false === filter_var($address, FILTER_VALIDATE_EMAIL)) {
                return ["error" => 1, "message" => '邮箱格式错误'];
            }
            $phpmailer->AddAddress($addressv);
        }
    } else {
        //验证邮件地址,非邮箱地址返回为false
        if (false === filter_var($address, FILTER_VALIDATE_EMAIL)) {
            return ["error" => 1, "message" => '邮箱格式错误'];
        }
        $phpmailer->AddAddress($address);
    }
    // 设置邮件标题
    $phpmailer->Subject = $subject;
    // 设置邮件正文,这里最好修改为这个，不是boby
    $phpmailer->MsgHTML($content);
    // 发送邮件。
    if (!$phpmailer->Send()) {
        return ["error" => 1, "message" => $phpmailer->ErrorInfo];
    }
    return ["error" => 0];
}

/**
 * QQ服务器发送邮件
 * @param  array $address 需要发送的邮箱地址 发送给多个地址需要写成数组形式
 * @param  string $subject 标题
 * @param  string $content 内容
 * @return array  放回状态吗和提示信息
 */
function send_email_qq($address, $subject, $content)
{
    $email_smtp_host = config('email.EMAIL_SMTP_HOST');
    $email_username = config('email.EMAIL_USERNAME');
    $email_password = config('email.EMAIL_PASSWORD');
    $email_from_name = config('email.EMAIL_FROM_NAME');
    $email_host = config('email.EMAIL_SEND_DOMAIN');
    if (empty($email_smtp_host) || empty($email_username) || empty($email_password) || empty($email_from_name)) {
        return ["error" => 1, "message" => '邮箱请求参数不全，请检测邮箱的合法性'];
    }
    //实例化PHPMailer核心类
    $phpmailer = new \PHPMailer();

    //是否启用smtp的debug进行调试 开发环境建议开启 生产环境注释掉即可 默认关闭debug调试模式
    $phpmailer->SMTPDebug = 0;

    //使用smtp鉴权方式发送邮件
    $phpmailer->IsSMTP();

    //smtp需要鉴权 这个必须是true
    $phpmailer->SMTPAuth = true;

    //设置使用ssl加密方式登录鉴权
    $phpmailer->SMTPSecure = 'ssl';

    // 设置SMTP服务器。
    $phpmailer->Host = $email_smtp_host;

    //设置ssl连接smtp服务器的远程服务器端口号，以前的默认是25，但是现在新的好像已经不可用了 可选465或587
    $phpmailer->Port = 465;

    //设置发件人的主机域 可有可无 默认为localhost 内容任意，建议使用你的域名
    $phpmailer->Hostname = $email_host;

    // 	设置邮件的字符编码'
    $phpmailer->CharSet = 'UTF-8';

    // 设置发件人名字
    $phpmailer->FromName = $email_username;

    //设置发件人姓名（昵称） 任意内容，显示在收件人邮件的发件人邮箱地址前的发件人姓名
    $phpmailer->Username = $email_username;

    //smtp登录的密码 使用生成的授权码（就刚才叫你保存的最新的授权码）
    $phpmailer->Password = $email_password;

    //设置发件人邮箱地址 这里填入上述提到的“发件人邮箱”
    $phpmailer->From = $email_username;

    //邮件正文是否为html编码 注意此处是一个方法 不再是属性 true或false
    $phpmailer->IsHTML(true);

//    //设置收件人邮箱地址 该方法有两个参数 第一个参数为收件人邮箱地址 第二参数为给该地址设置的昵称 不同的邮箱系统会自动进行处理变动 这里第二个参数的意义不大
//    $phpmailer->addAddress($address,'lsgo在线通知');

    // 添加收件人地址，可以多次使用来添加多个收件人
    if (is_array($address)) {
        foreach ($address as $addressv) {
            //验证邮件地址,非邮箱地址返回为false
            if (false === filter_var($address, FILTER_VALIDATE_EMAIL)) {
                return ["error" => 1, "message" => '邮箱格式错误'];
            }
            $phpmailer->AddAddress($addressv, 'lsgo在线通知');
        }
    } else {
        //验证邮件地址,非邮箱地址返回为false
        if (false === filter_var($address, FILTER_VALIDATE_EMAIL)) {
            return ["error" => 1, "message" => '邮箱格式错误'];
        }
        $phpmailer->AddAddress($address, 'lsgo在线通知');
    }
    // 设置邮件标题
    $phpmailer->Subject = $subject;
    // 设置邮件正文,这里最好修改为这个，不是boby
//    $phpmailer->MsgHTML($content);

    //添加邮件正文 上方将isHTML设置成了true，则可以是完整的html字符串 如：使用file_get_contents函数读取本地的html文件
    $phpmailer->Body = $content;

    // 发送邮件。
    if (!$phpmailer->Send()) {
        return ["error" => 1, "message" => $phpmailer->ErrorInfo];
    }
    return ["error" => 0];
}

/**
 * QQ服务器发送邮件
 * @param  array $address 需要发送的邮箱地址 发送给多个地址需要写成数组形式
 * @param  string $subject 标题
 * @param  string $content 内容
 * @return array  放回状态吗和提示信息
 */
function send_email_qq2($address, $subject, $content)
{
//    $email_smtp_host = config('email.EMAIL_SMTP_HOST');
    $email_smtp_host = 'smtp.qq.com';
//    $email_username = config('email.EMAIL_USERNAME');
    $email_username = '1722318623@qq.com';
//    $email_password = config('email.EMAIL_PASSWORD');
    $email_password = 'znjuxdrcxupxbegi';
    $email_from_name = config('email.EMAIL_FROM_NAME');
    $email_host = config('email.EMAIL_SEND_DOMAIN');
    if (empty($email_smtp_host) || empty($email_username) || empty($email_password) || empty($email_from_name)) {
        return ["error" => 1, "message" => '邮箱请求参数不全，请检测邮箱的合法性'];
    }
    //实例化PHPMailer核心类
    $phpmailer = new \PHPMailer();

    //是否启用smtp的debug进行调试 开发环境建议开启 生产环境注释掉即可 默认关闭debug调试模式
    $phpmailer->SMTPDebug = 1;

    //使用smtp鉴权方式发送邮件
    $phpmailer->IsSMTP();

    //smtp需要鉴权 这个必须是true
    $phpmailer->SMTPAuth = true;

    //设置使用ssl加密方式登录鉴权
    $phpmailer->SMTPSecure = 'ssl';

    // 设置SMTP服务器。
    $phpmailer->Host = $email_smtp_host;

    //设置ssl连接smtp服务器的远程服务器端口号，以前的默认是25，但是现在新的好像已经不可用了 可选465或587
    $phpmailer->Port = 465;

    //设置发件人的主机域 可有可无 默认为localhost 内容任意，建议使用你的域名
    $phpmailer->Hostname = $email_host;

    // 	设置邮件的字符编码'
    $phpmailer->CharSet = 'UTF-8';

    // 设置发件人名字
    $phpmailer->FromName = $email_username;

    //设置发件人姓名（昵称） 任意内容，显示在收件人邮件的发件人邮箱地址前的发件人姓名
    $phpmailer->Username = $email_username;

    //smtp登录的密码 使用生成的授权码（就刚才叫你保存的最新的授权码）
    $phpmailer->Password = $email_password;

    //设置发件人邮箱地址 这里填入上述提到的“发件人邮箱”
    $phpmailer->From = $email_username;

    //邮件正文是否为html编码 注意此处是一个方法 不再是属性 true或false
    $phpmailer->IsHTML(true);

    //设置收件人邮箱地址 该方法有两个参数 第一个参数为收件人邮箱地址 第二参数为给该地址设置的昵称 不同的邮箱系统会自动进行处理变动 这里第二个参数的意义不大
    $phpmailer->addAddress($address, 'lsgo在线通知');

    // 添加收件人地址，可以多次使用来添加多个收件人
//    if (is_array($address)) {
//        foreach ($address as $addressv) {
//            //验证邮件地址,非邮箱地址返回为false
//            if (false === filter_var($address, FILTER_VALIDATE_EMAIL)) {
//                return ["error" => 1, "message" => '邮箱格式错误'];
//            }
//            $phpmailer->AddAddress($addressv);
//        }
//    } else {
//        //验证邮件地址,非邮箱地址返回为false
//        if (false === filter_var($address, FILTER_VALIDATE_EMAIL)) {
//            return ["error" => 1, "message" => '邮箱格式错误'];
//        }
//        $phpmailer->AddAddress($address);
//    }
    // 设置邮件标题
    $phpmailer->Subject = $subject;
    // 设置邮件正文,这里最好修改为这个，不是boby
//    $phpmailer->MsgHTML($content);

    //添加邮件正文 上方将isHTML设置成了true，则可以是完整的html字符串 如：使用file_get_contents函数读取本地的html文件
    $phpmailer->Body = $content;

    // 发送邮件。
    if (!$phpmailer->Send()) {
        return ["error" => 1, "message" => $phpmailer->ErrorInfo];
    }
    return ["error" => 0];
}

/** 格式化时间
 * @param $time
 * @return false|string
 * @static
 */
function getCurrentDate()
{
    return date('Y-m-d H:i:s', time());
}

//--------------------------------------------------auth2.0------------------------------------------------------------
/**
 * 获取github信息
 */
function oauth_github()
{
    $github_url = config('github.OAUTH_URL');
    // 这个参数是必须的，这就是我们在第一步注册应用程序之后获取到的Client ID；
    $client_id = config('github.OAUTH_URL');
    // 该参数可选，当我们从Github获取到code码之后跳转到我们自己网站的URL
    $redirect_uri = config('github.OAUTH_URL');
    $url = $github_url . "?client_id=" . $client_id . "&redirect_uri=" . $redirect_uri;
    header('location:' . $url);
}

/**
 * github 回调地址
 * @param Request $request
 */
function github_redirect_uri(Request $request)
{
    //'code' => string '137b34c45d7282436d53'
    $code = $request->get('code');
    $client_id = config('github.OAUTH_URL');
    $client_secret = config('github.OAUTH_URL');
    $access_token_url = config('github.ACCESS_TOKEN_URL');
    //第一步:取全局access_token
    $postRes = $this->curl_request($access_token_url, [
        "client_id" => $client_id,
        "client_secret" => $client_secret,
        "code" => $code,
    ]);
    //第三步:根据全局access_token和openid查询用户信息
    $jsonRes = json_decode($postRes, true);
    $access_token = $jsonRes["access_token"];
    $userUrl = config('github.USER_INFO_URL') . "?access_token=" . $access_token;
    $userInfo = $this->curl_request($userUrl);
    $userJsonRes = json_decode($userInfo, true);
    //第五步，如何设置Wordpress中登录状态
    return $userJsonRes;
}


/**
 * 获得日志数据表数据表配置
 * @return array
 */
function get_global_db_config()
{
    $global_db_config = array(
        'log_table' => config('database.log_table'),
        'username' => config('database.username'),
        'password' => config('database.password'),
        'hostname' => config('database.hostname'),
        'database' => config('database.database'),
    );
    return $global_db_config;
}

/**
 * 记录操作日志
 * @param null $desc
 * @param string $unique_flag
 * @param $app
 * @param $action
 * @param $method
 * @return bool
 */
function add_operation_log($desc = null, $unique_flag = 'system')
{
    $instance = \think\Request::instance();
    $module = $instance->module();
    $controller = $instance->controller();
    $action = $instance->action();
    $config = get_global_db_config();
    $conn = new \mysqli($config['hostname'], $config['username'], $config['password'], $config['database']);
    if ($conn->connect_error) {
        die("连接失败: " . $conn->connect_error);
    }
    mysqli_query($conn, 'set names utf8');
    $account = get_admin_account();
    $nickname = get_admin_nickname();
    $user_id = get_admin_user_id();
    $ipaddr = request()->ip();
    $query_string = implode('--', array_merge($_GET, $_POST, input()));
    $is_desc = 0;

    if ($desc) $is_desc = 1;
    $insert_time = date('Y-m-d H:i:s');
    $query = "INSERT INTO `" . $config['log_table'] . "` (`guid`,`account`,`nickname`,`addtime`,`module`,`controller`,
    `action`,`query_string`,`is_desc`,`desc`,`ipaddr`,`unique_flag`) VALUES ('$user_id','$account','$nickname','$insert_time','$module',
    '$controller','$action','$query_string','$is_desc','$desc','$ipaddr','$unique_flag');";
    if (mysqli_query($conn, $query)) {
        $result = TRUE;
    } else {
        $result = "Error:" . $query . "<br/>" . mysqli_error($conn);
        //用于以后调试
//        $result = FALSE;
    }
    return $result;
}

/**
 * @return mixed
 */
function get_admin_account()
{
    return session('admin.username');
}

/**
 * 获取用户昵称
 * @return mixed
 */
function get_admin_nickname()
{
    return session('admin.username');
}


/**
 * 获取用户ID信息，这条信息是用户登陆的时候保存的
 * @return mixed
 */

function get_admin_user_id()
{
    return session('admin.admin_id');
}


/*************************************经验值转换为等级 **********************************
 *
 * @param  [type] $exp [description]
 * @return [type]      [description]
 */
function points_to_level($exp)
{
    switch (true) {
        case $exp >= config('points.LV20') :
            return LV20;
        case $exp >= config('points.LV19') :
            return 19;
        case $exp >= config('points.LV18') :
            return 18;
        case $exp >= config('points.LV17') :
            return 17;
        case $exp >= config('points.LV16') :
            return 16;
        case $exp >= config('points.LV15') :
            return 15;
        case $exp >= config('points.LV14') :
            return 14;
        case $exp >= config('points.LV13') :
            return 13;
        case $exp >= config('points.LV12') :
            return 12;
        case $exp >= config('points.LV11') :
            return 11;
        case $exp >= config('points.LV10') :
            return 10;
        case $exp >= config('points.LV9') :
            return 9;
        case $exp >= config('points.LV8') :
            return 8;
        case $exp >= config('points.LV7') :
            return 7;
        case $exp >= config('points.LV6') :
            return 6;
        case $exp >= config('points.LV5') :
            return 5;
        case $exp >= config('points.LV4') :
            return 4;
        case $exp >= config('points.LV3') :
            return 3;
        case $exp >= config('points.LV2') :
            return 2;
        default :
            return 1;
    }
}

/**
 * -------------------------------------------------文件管理------------------------------------------------------------
 */

/**
 * 转换字节大小 Bytes/Kb/MB/GB/TB/EB
 * @param $size
 * @return string
 */
function trans_byte($size)
{
    $size_arr = ["B", "KB", "MB", "GB", "TB", "EB"];
    $i = 0;
    while ($size >= 1024) {
        $size = $size / 1024;
        $i++;
    }
    return round($size, 2) . $size_arr[$i];
}

/**
 * ----------------------------------------------------短信-------------------------------------------------------------
 */

/**
 * 阿里大于发送模板
 * @param $tel   电话号码，如：13669361192
 * @param $type  发送模板类型，如：live
 * @param $data  模板发送的数据，如：["number" => '89', 'code' => "888888"]
 * @return mixed
 */
function send_dayu_sms($tel, $type, $data)
{
    $dayu_template = 'template_' . $type; //template_register
    $signname = config("sms")['dayu'][$dayu_template]["sign_name"];
    $templatecode = config("sms")['dayu'][$dayu_template]["code"];
    $config = [
        'app_key' => config("sms")['dayu']['app_key'], //阿里大于APPKEY
        'app_secret' => config("sms")['dayu']['app_secret'] //阿里大于secretKey
    ];
    $client = new \Flc\Alidayu\Client(new \Flc\Alidayu\App($config));
    $req = new \Flc\Alidayu\Requests\AlibabaAliqinFcSmsNumSend();
    $req->setRecNum("{$tel}");
    switch ($type) {
        case 'register':
            $req->setSmsParam('{"code":"' . $data['code'] . '"}');
            break;
        case 'live':
            $req->setSmsParam('{"number":"' . $data['number'] . '","code":"' . $data['code'] . '"}');
            break;
        case 'identity':
            $req->setSmsParam('{"name":"' . $data['name'] . '"}');
            break;
        default:
            $req->setSmsParam('{"code":"' . $data['code'] . '","product":"' . $data['product'] . '"}');
    }
    $req->setSmsFreeSignName("{$signname}");
    $req->setSmsTemplateCode("{$templatecode}");
    $resp = $client->execute($req);
    return $resp;
}

/**
 * --------------------------------------------------方法注入------------------------------------------------------------
 */

// 通过hook方法注入动态方法
\think\Request::hook('user', 'getUserInfo');

//根据$userId获取用户信息
function getUserInfo(\think\Request $request, $userId)
{
    // 根据$userId获取用户信息
    return \think\Db::table('resty_open_user')->where('id', $userId)->find();
}


/**
 * --------------------------------------------------钩子动态绑定--------------------------------------------------------
 */
//use think\Hook;
//
//Hook::add('app_init',[
//    '\app\common\behavior\Test',
//    '\app\common\behavior\Hello',
//]);
//Hook::add('app_begin',[
//    '\app\common\behavior\Test',
//]);
//Hook::add('module_init',[
//    function($request){
//        echo 'hello,'.$request->module().'!<br/>';
//    },
//]);
