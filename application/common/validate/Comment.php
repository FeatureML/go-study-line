<?php
/**
 * Created by PhpStorm.
 * User: tinywan
 * Date: 2017/7/9
 * Time: 11:05
 */

namespace app\common\validate;

use think\Validate;

class Comment extends Validate
{
    protected $rule = [
        'comment_content' => 'require'
    ];

    protected $message = [
        'comment_content.require' => "评论内容不能为空",
    ];
}