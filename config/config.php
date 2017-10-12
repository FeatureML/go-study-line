<?php
return [
    'app_status'          => 'office',    //home office  通过这里可以切换配置文件home.php 和office.php的配置文件
    'app_author'          => 'Tinywan',
    'app_email'          => '756684177@qq.com',
    'app_debug'              => true,
    // 默认的空控制器名
    'empty_controller'       => 'Error',
    // 入口自动绑定模块
    //'auto_bind_module'       => true,
    // 是否开启路由
    'url_route_on'           => true,
    // 设置路由配置文件列表
    'route_config_file'	=>	['frontend','backend','blog'],
    // 域名根，如thinkphp.cn
    'url_domain_root'        => 'tinywan.top',
    // 是否强制使用路由
    // 'url_route_must'         => false
    // 默认模块名
    'default_module'         => 'blog',
    // 是否自动转换URL中的控制器和操作名
    'url_convert'            => false,
    'captcha'  => [
        // 验证码字符集合
        'codeSet'  => '12345',
        //	使用中文验证码
//        'useZh' => true,
        //中文验证码字符串
//        'zhSet'  => '服务器端程序在性能问题上应该有何种基本思路',
        // 验证码字体大小(px)
        'fontSize' => 30,
        // 是否画混淆曲线
        'useCurve' => true,
        // 验证码图片高度
        'imageH'   => 50,
        // 验证码图片宽度
        'imageW'   => 300,
        // 验证码位数
        'length'   => 3,
        'bg'   => [243, 251, 254],
        // 验证成功后是否重置
        'reset'    => true
    ],
    //独立配置文件数据库配置文件
    'extra_config_list'     => ['database','session'],
    // 默认跳转页面对应的模板文件
    'dispatch_success_tmpl'  => APP_PATH . 'common' . DS . 'view' . DS . 'tpl' . DS . 'dispatch_jump.tpl',
    'dispatch_error_tmpl'    => APP_PATH . 'common' . DS . 'view' . DS . 'tpl' . DS . 'dispatch_jump.tpl',
];
