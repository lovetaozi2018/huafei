<?php

namespace app\common\model;

use think\Db;
use think\Model;

class UserOrder extends Model
{
    protected $table = 'hf_user_order';

    /**
     * 充值余额
     *
     * @param array $post
     * @return bool
     */
    public function recharge(array $post)
    {
        $data = [
            'user_id' => $post['user_id'],
            'type' => 1,
            'amount' => $post['money'],
            'ctime' => date('Y-m-d H:i:s',time()),
            'order_no' => getOrderNo(),
        ];
        $res = $this->allowField(true)->save($data);
        return $res ? true : false;
    }

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

    /**
     * 资金明细
     *
     * @param $uid
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function fundsDetail($uid)
    {
        $data = [];
        $page = input('page') ? input('page') : 1;
        $pageSize = input('page_size') ? input('page_size') : 10;
        $limit = ($page - 1) * $pageSize;
        $orders = Db::name('user_order')->where('user_id',$uid)->field('user_id,amount,ctime,status,type')->select();
        $recharge = Db::name('mobile_recharge')->where('user_id',$uid)->field('user_id,price,ctime,status')->select();
        foreach ($recharge as $k=> $v){
            $recharge[$k]['type'] = 4; //话费充值
            $recharge[$k]['amount'] = $v['price']; //话费充值
            unset( $recharge[$k]['price']);
        }
        $orders = array_merge($orders,$recharge);

        $time = array_column($orders,'ctime');
        array_multisort($time,SORT_DESC,$orders);
        $data = array_slice($orders,$limit,$pageSize);

        return $data;

    }
}