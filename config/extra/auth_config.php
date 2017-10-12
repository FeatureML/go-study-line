<?php
/**
 * Created by PhpStorm.
 * User: tinywan
 * Date: 2017/6/23
 * Time: 22:07
 * ****************************支付宝信息**********************************
 */
return [
    'db_prefix' => 'resty_',
    'auth_on' => true,
    'auth_type' => 2,
    'auth_group' => 'auth_group',
    'auth_group_access' => 'auth_group_access',
    'auth_rule' => 'auth_rule',
    'auth_user' => 'user',
    // 全部为小写
    'open_auth' => [
        'entry/index',
        'entry/modpass',
        'login/Login'
    ]
];