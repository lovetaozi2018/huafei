<?php

namespace app\common\model;

use think\Model;

class UserOrder extends Model
{
    protected $table = 'hf_user_order';

    /**
     * 申请提现
     *
     * @param array $post
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function applyWithdraw(array $post)
    {
        $this->startTrans();
        $user = User::where('id',$post['user_id'])->find();
        if($user['bonus'] < $post['amount']){
            $this->error = '提现金额不足';
            return false;
        }
        $data = [
            'user_id' => $post['user_id'],
            'amount' => $post['amount'],
            'phone' => $user['phone'],
            'order_no' => getOrderNo(),
        ];
        $res = $this->allowField(true)->save($data);
        if(!$res){
            $this->error = '申请失败';
            $this->rollback();
            return false;
        }
        $user->bonus = ($user['bonus']-$post['amount']);
        $re = $user->save();
        if(!$re){
            $this->error = '扣除奖金余额失败';
            $this->rollback();
            return false;
        }
        $this->commit();
        return true;
    }

    /**
     * 奖金划转
     *
     * @param array $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function transfer(array $data)
    {
        $user = User::where('id',$data['user_id'])->find();
        if($user['bonus'] < $data['amount']){
            $this->error = '金额不足';
            return false;
        }
        $user->bonus = ($user['bonus']-$data['amount']);
        $user->money = ($user['money']+$data['amount']);
        $res = $user->save();
        return $res ? true : false;
    }
}