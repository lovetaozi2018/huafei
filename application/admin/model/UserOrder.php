<?php

namespace app\admin\model;

use app\common\model\UserBonus;
use app\common\model\MemberSet;
use think\Model;

class UserOrder extends Model
{
    protected $table='hf_user_order';

    public function user()
    {
        return $this->belongsTo('User','user_id','id');
    }

    /**
     * 用户充值成功之后手动确认充值，修改订单状态
     *
     * @param $orderId
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function addRecharge($orderId)
    {
        $this->startTrans();
        $order = $this->where('id',$orderId)->find();
        $order->status = 1;
        if(!$order->save()){
            $this->error = '订单状态修改失败';
            return false;
        }
        $user = User::where('id',$order['user_id'])->find();
        $user->bonus = $order['amount'] + $user['bonus'];
        $res = $user->save();
        if(!$res){
            $this->error = '充值失败';
            $this->rollback();
            return false;
        }

        $this->commit();
        return true;
    }

    /**
     * 提现成功之后手动确认提现，修改订单状态
     *
     * @param $orderId
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function withdraw($orderId)
    {
        $this->startTrans();
        $order = $this->where('id',$orderId)->find();
        $order->status = 1;
        if(!$order->save()){
            $this->error = '订单状态修改失败';
            $this->rollback();
            return false;
        }
        $this->commit();
        return true;
    }

    /**
     * 提现驳回
     *
     * @param $orderId
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function refuse($orderId)
    {
        $this->startTrans();
        $order = $this->where('id',$orderId)->find();
        $order->status = 2;
        if(!$order->save()){
            $this->error = '订单状态修改失败';
            return false;
        }
        $user = User::where('id',$order['user_id'])->find();
        $user->bonus = $order['amount'] + $user['bonus'];
        $res = $user->save();
        if(!$res){
            $this->error = '驳回失败';
            $this->rollback();
            return false;
        }

        $this->commit();
        return true;
    }



}
