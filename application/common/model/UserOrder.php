<?php

namespace app\common\model;

use app\admin\model\User;
use think\Db;
use think\Model;

class UserOrder extends Model
{
    protected $table = 'hf_user_order';

    /**
     * 奖金账户充值
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
            'type' => 2, //提现
            'ctime' => date('Y-m-d H:i:s',time()),
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
     * 把奖金划转到话费账户
     *
     * @param $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function transfer($data)
    {
        $this->startTrans();
        $user = User::where('id',$data['user_id'])->find();
        if($user['bonus'] < $data['amount']){
            $this->error = '金额不足';
            return false;
        }
        // 添加划转订单
        $result = $this->allowField(true)->save([
            'user_id' => $user['id'],
            'amount' => $data['amount'],
            'type' => 3,
            'ctime' => date('Y-m-d H:i:s',time()),
            'order_no' => getOrderNo(),
            'status' => 1,
        ]);
        if(!$result){
            $this->error = '订单创建失败';
            $this->rollback();
            return false;
        }
        // 修改用户余额
        $user->bonus = ($user['bonus']-$data['amount']);
        $user->money = ($user['money']+$data['amount']);
        $setModel = new MemberSet();
        // 获取充值金额能达到的会员等级
        $getMemberId = $setModel->getMemberId($data['amount']);
        if($getMemberId){
            $getMember = MemberSet::where('id',$getMemberId)->find();
            $memberId = $user['member_id'];
            $member = MemberSet::where('id',$memberId)->find(); //当前会员等级
            if($getMember['level'] > $member['level']){ //如果会员等级提高，则更新会员等级
                $user->member_id = $getMemberId;
            }
        }
        $res = $user->save();
        if(!$res){
            $this->error = '划转失败';
            $this->rollback();
            return false;
        }
        // 如果会员等级提升,则发生对碰
        if($getMemberId && ($getMember['level'] > $member['level'])){
            $model = new UserBonus();
            $userModel = new User();
            $userBonus = $model->settleBonus($data['id']);
            $rows = [];
            if(sizeof($userBonus) != 0){
                $res = $model->allowField(true)->insertAll($userBonus);
                if(!$res){
                    $this->error = '对碰结算失败';
                    $this->rollback();
                    return false;
                }
                foreach ($userBonus as $u){
                    $father = $this->where('id',$u['father_id'])->find();
                    $rows[] = [
                        'id' => $u['father_id'],
                        'bonus' => $father['bonus'] + $u['amount'],
                    ];
                }
                // 结算奖金，修改用户奖金
                $re = $userModel->saveAll($rows);
                if(!$re){
                    $this->error = '用户奖金结算失败';
                    $this->rollback();
                    return false;
                }
            }

        }

        $this->commit();
        return true;
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