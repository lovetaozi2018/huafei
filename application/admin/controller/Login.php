<?php

namespace app\admin\controller;

use app\common\model\Admin;
use think\Controller;
use think\facade\Session;

class Login extends Controller
{
    public function index()
    {
        return view();
    }

    /**
     * 登录
     *
     * @return \think\response\Json
     */
    public function login()
    {
        $data = input();
        $model = new Admin();
        $res = $model->login($data);
        if(!$res){
            return json(['code'=> 201,'msg' => $model->getError()]);
        }
        session('user', $res);
        return json(['code' => 200, 'msg' => '登录成功', 'url' => url('/')]);
    }

    public function logout()
    {
        Session::clear();
        return redirect("login/index");
    }

}