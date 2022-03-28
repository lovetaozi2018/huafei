<?php

namespace app\api\controller;

use app\common\model\UserCoupons;
use think\Db;
use think\facade\Env;

class Coupons extends Base
{
    public function index()
    {
        $date = date('Y-m-d',time());
        $page = input('page') ? input('page') : 1;
        $pageSize = input('page_size') ? input('page_size') : 10;
        $limit = ($page - 1) * $pageSize;

        $userId = $this->user['id'];
        $coupons = Db::name('mobile_coupons')->where('start_date','<=',$date)
            ->where('end_date','>=',$date)
            ->limit($limit,$pageSize)
            ->order('id desc')
            ->select();
        foreach ($coupons as $k=>$v){
            $coupons[$k]['logo'] = $v['logo'] ? Env::get('api_path') .$v['logo'] : '';
            $coupon = Db::name('user_coupons')->where('user_id',$userId)
                ->where('coupons_id',$v['id'])
                ->find();
            $status = $coupon ? ($coupon['status'] ? 2 : 1) : 0;
            $coupons[$k]['status'] = $status; // 0:未领取，2:未使用,1:已使用
            $coupons[$k]['user_coupons_id'] = $status==1 ? $coupon['id'] : 0; // 0:未领取，2:未使用,1:已使用

        }

        return json(['code' => 200,'data' => $coupons]);
    }

    public function myCoupons()
    {
        $date = date('Y-m-d',time());
        $page = input('page') ? input('page') : 1;
        $pageSize = input('page_size') ? input('page_size') : 10;
        $limit = ($page - 1) * $pageSize;

        $userId = $this->user['id'];
        $model  = new  UserCoupons();
        $coupons = $model->where('user_id',$userId)
            ->limit($limit,$pageSize)
            ->order('id desc')
            ->select();
        foreach ($coupons as $k=>$v){
            $coupon = $v->coupons;
            $coupons[$k]['logo'] = $coupon['logo'] ? Env::get('api_path') .$coupon['logo'] : '';
            $coupons[$k]['title'] = $coupon['title'];
            $coupons[$k]['content'] = $coupon['content'];
            $coupons[$k]['start_date'] = $coupon['start_date'];
            $coupons[$k]['end_date'] = $coupon['end_date'];
            $coupons[$k]['zhekou'] = $coupon['zhekou'];
            if($date>$coupon['end_date']){
                $coupons[$k]['status']  = 2;//已过期
            }
            unset( $coupons[$k]['coupons']);
        }
        return json(['code' => 200,'data' => $coupons]);
    }

    /**
     * 领取优惠券
     *
     * @return \think\response\Json
     */
    public function receiveCoupons()
    {
        $post = input();
        $post['user_id'] = $this->user['id'];
        $model = new UserCoupons();
        $res = $model->adds($post);
        return $res ? json(['code' => 200,'msg' => '领取成功']) : json(['code' => 201,'msg' => '领取失败']);
    }

}