<?php
namespace app\api\validate;

use think\Validate;

class User extends Validate
{
    protected $rule = [
        'phone|手机号' => 'require|unique:user',
        'password|密码' => 'require|length:6,36',
        'code|验证码' => 'require',
    ];

    protected $scene = [
        'login' => ['phone', 'password'],
        'register' => ['phone', 'password','code'],
    ];

}