<?php

namespace app\admin\controller;

use think\App;
use think\Controller;

class Index extends Controller
{
    public function index()
    {
        $this->redirect('login/index');
    }
}