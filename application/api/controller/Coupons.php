<?php

namespace app\api\controller;

use think\Controller;
use think\Db;
use think\facade\Env;

class Coupons extends Controller
{
    public function index()
    {
        $date = date('Y-m-d',time());
        $page = input('page') ? input('page') : 1;
        $pageSize = input('page_size') ? input('page_size') : 10;
        $limit = ($page - 1) * $pageSize;

        $coupons = Db::name('mobile_coupons')->where('start_date','<=',$date)
            ->where('end_date','>=',$date)
            ->limit($limit,$pageSize)
            ->order('id desc')
            ->select();
        foreach ($coupons as $k=>$v){
            $coupons[$k]['logo'] = $v['logo'] ? Env::get('api_path') .$v['logo'] : '';
        }

        return json(['code' => 200,'data' => $coupons]);
    }
}