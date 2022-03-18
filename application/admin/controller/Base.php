<?php

namespace app\admin\controller;

use think\App;
use think\Controller;
use think\Db;
use think\facade\Session;

class Base extends Controller
{
    protected $user;

    function __construct(App $app = null)
    {
        parent::__construct($app);
        if(!session('user')){
            $this->redirect("login/index");
        }
        $this->user = Session::get('user');
        // 查询待处理的话费订单
        $num1 =Db::name('user_order')->where('type',1)
            ->where('status',0)
            ->count();
        // 查询待处理的提现订单
        $num2 =Db::name('user_order')->where('type',2)
            ->where('status',0)
            ->count();
        $num3 = Db::name('mobile_recharge')->where('status',0)
            ->count();
        $this->assign('num1',  $num1);
        $this->assign('num2',  $num2);
        $this->assign('num3',  $num3);
        $this->assign('user',  $this->user);
    }
}