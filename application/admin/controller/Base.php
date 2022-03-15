<?php

namespace app\admin\controller;

use think\App;
use think\Controller;

class Base extends Controller
{
    protected $user;

    function __construct(App $app = null)
    {
        parent::__construct($app);
//        if(!session('user')){
//            $this->redirect("login/index");
//        }
//        $this->user = session('user');
//        $this->assign('user',  $this->user);
    }
}