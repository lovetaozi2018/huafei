<?php

namespace app\common\model;

use think\Model;

class Admin extends Model
{
    protected $table = 'hf_admin';

    public function login($data)
    {
        $admin = $this->where([['username', '=', $data['username']]])->find();
        if (!$admin) {
            $this->error = '账号错误';
            return false;
        }

        $pwd = md5_pass($data['password']);

        if ($pwd != $admin['password']) {
            $this->error = '密码错误';
            return false;
        }

        return true;
    }

}