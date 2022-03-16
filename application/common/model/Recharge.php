<?php

namespace app\common\model;

use think\Model;

class Recharge extends Model
{
    protected $table = 'hf_mobile_recharge';

    /**
     * 话费充值
     *
     * @param array $post
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function recharge(array $post)
    {
        $this->startTrans();
        $user = User::where('id',$post['user_id'])->find();
        if($user['money'] < $post['real_price']){
            $this->error = '余额不足';
            return false;
        }
        $orderNo = 'H'.substr(microtime(true) * 10000,6,8).rand(1000,9999);
        $data = [
            'user_id' => $post['user_id'],
            'phone' => $post['phone'],
            'price' => $post['price'],
            'real_price' => $post['real_price'],
            'order_no' => $orderNo,
            'month' => date('Y-m',time()),
            'date' => date('Y-m-d',time()),
        ];
        $re = $this->allowField(true)->save($data);
        if(!$re){
            $this->error ='失败';
            $this->rollback();
            return false;
        }
        $user->money = ($user['money']-$post['real_price']);
        $res = $user->save();
        if(!$res){
            $this->error = '余额扣除失败';
            $this->rollback();
            return false;
        }
        $this->commit();

        return true;
    }

    /**
     * 话费订单列表
     *
     * @param $userId
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function lists($userId)
    {
        $page = input('page') ? input('page') : 1;
        $pageSize = input('page_size') ? input('page_size') : 5;
        $limit = ($page - 1) * $pageSize;
        $lists = $this->where('user_id',$userId)
            ->order('id desc')
            ->limit($limit,$pageSize)
            ->select();
        $month = $data = [];
        foreach ($lists as $v){
            $month[] = $v['month'];
        }
        $month = array_unique($month);
        foreach ($month as $m) {
            foreach ($lists as $k=>$v) {
                if($v['month'] == $m){
                    $data[$m][] = $v;
                }
            }
        }

        return $data;
    }
}