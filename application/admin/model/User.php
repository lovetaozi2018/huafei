<?php

namespace app\admin\model;

use app\common\model\Recharge;
use app\common\model\UserBonus;
use app\common\model\MemberSet;
use Think\Db;
use think\Model;

class User extends Model
{
    protected $table='hf_user';

    /**
     * 用户会员充值
     *
     * @param $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \Exception
     */
    public function addRecharge($data)
    {
        $this->startTrans();
        $user = User::where('id',$data['user_id'])->find();
        $user->money = $data['amount'] + $user['money'];
        $setModel = new MemberSet();
        // 获取充值金额能达到的会员等级
        $getMemberId = $setModel->getMemberId($data['amount']);
        if($getMemberId){
            $getMember = MemberSet::where('id',$getMemberId)->find();
            $memberId = $user['member_id'];
            $member = MemberSet::where('id',$memberId)->find();
            if($getMember['level'] > $member['level']){ //如果会员等级提高，则更新会员等级
                $user->member_id = $getMemberId;
            }
        }
        $res = $user->save();
        if(!$res){
            $this->error = '充值失败';
            return false;
        }
        // 如果会员等级提升,则发生对碰
        if($getMemberId && ($getMember['level'] > $member['level'])){
            $model = new UserBonus();
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

                $re = $this->saveAll($rows);
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

    public function orders()
    {
        return $this->hasMany('UserOrder','user_id','id');
    }

    public function father()
    {
        return $this->hasOne('User','id','father_id');
    }

    public function members($uid = 0)
    {
        $where = [];
        if($uid){
            $childIds = $this->getChildren($uid);
            $where[] = ['id','in',$childIds];
        }
        $totalMoney = $totalBonus  = 0;
        $model = new Recharge();
        $orderModel = new UserOrder();
        $bonusModel = new UserBonus();
        $members = $this->field('id,phone,real_name,username,member_id,father_id,money,bonus')->where($where)->select();
        foreach ($members as $k => $m){
            $father = $m->father;
            $members[$k]['father'] = $father ? $father['real_name'] : '';
            $child = $this->getChildren($m['id']);
            $child = $child ? $child : [];
            $child = array_unique($child);
            $members[$k]['child_total'] = count($child); //子级总数
            array_push($child,$m['id']);
            $bonusTotal = $this->where('id','in',$child)->sum('bonus');
            $members[$k]['last_bonus_total'] = $bonusTotal; // 剩余奖金统计
            $totalBonus +=$bonusTotal;
            $members[$k]['bonus_total'] = $bonusModel->where('user_id','in',$child)->sum('amount'); // 对碰奖金统计
            $members[$k]['tixian_total'] = $orderModel->where('user_id','in',$child)
                ->where('type',2)
                ->where('status',1)
                ->sum('amount'); // 提现统计
            $members[$k]['money_total'] = $orderModel->where('user_id','in',$child)
                ->where('type','in',[1,3])
                ->where('status',1)
                ->sum('amount'); // 充值统计
            $moneyTotal = $this->where('id','in',$child)->sum('money');
            $members[$k]['last_money_total'] = $moneyTotal; // 余额统计
            $totalMoney += $moneyTotal;
            $members[$k]['recharge_total'] = $model->where('user_id','in',$child)
                ->where('status',1)
                ->sum('price'); // 话费充值统计
            unset($GLOBALS['childIds']);

        }
        return [
            'members' => $members,
            'total_child' => count($members),
            'total_bonus' => $totalBonus,
            'total_money' => $totalMoney,
        ];

    }

    /**
     * 获取用户所有的子级id
     *
     */
    public function getChildren($uid, $childIds = [])
    {
        global $childIds;
        $childrens = Db::name('user')->where(['father_id' => $uid])->select();
        if ($childrens) {
            foreach ($childrens as $d) {
                $childIds[] = $d['id'];
                $this->getChildren($d['id'],$childIds);
            }
        }
        return $childIds;
    }


}
