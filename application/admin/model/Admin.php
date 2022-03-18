<?php

namespace app\admin\model;

use think\Model;

class Admin extends Model
{
    protected $table='hf_admin';

    public function modifyPassword($post)
    {
        $pwd = $this->user['password'];
        if(md5_pass($post['pwd'] != $pwd)){
            $this->error = '原密码错误';
            return false;
        }
        $admin = $this->where('id',$this->user['id'])->find();
        $password = md5_pass($post['password']);
        $admin->password = $password;
        $res = $admin->save();
        if(!$res){
            $this->error = '修改失败';
            return false;
        }
        return true;
    }
}
