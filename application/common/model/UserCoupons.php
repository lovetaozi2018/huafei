<?php

namespace app\common\model;

use Think\Db;
use think\facade\Env;
use think\Model;

class UserCoupons extends Model
{
    /**
     * @param array $post
     * @return bool
     */
    public function adds(array $post)
    {
        $res = $this->allowField(true)->save($post);
        return $res ? true : false;
    }

    /**
     * 获取用户优惠券
     *
     * @param $userId
     * @return UserCoupons[]|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getCoupons($userId)
    {
        $date = date('Y-m-d',time());
        $coupons = Db::name('mobile_coupons')->where('start_date','<=',$date)
            ->where('end_date','>=',$date)
            ->order('id desc')
            ->select();
        $ids = [];
        if(sizeof($coupons) != 0){
            foreach ($coupons as $k=>$v){
                $ids[] = $v['id'];
            }
        }
        $userCoupons = $this->where('coupons_id','in',$ids)
            ->where('user_id',$userId)
            ->where('status',0)
            ->select();
        if(sizeof($userCoupons) != 0){
            foreach ($userCoupons as $k=>$v){
               $coupon = $v->coupons;
               $userCoupons[$k]['zhekou'] = $coupon['zhekou'];
               $userCoupons[$k]['content'] = $coupon['content'];
               $userCoupons[$k]['start_date'] = $coupon['start_date'];
               $userCoupons[$k]['end_date'] = $coupon['end_date'];
               $userCoupons[$k]['logo'] = $coupon['logo'] ? Env::get('api_path').$coupon['logo'] : '';
            }
            unset($userCoupons[$k]['coupons']);
        }

        return $userCoupons;

    }

    public function coupons()
    {
        return $this->belongsTo('MobileCoupons','coupons_id','id');
    }
}