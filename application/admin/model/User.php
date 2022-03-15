<?php

namespace app\admin\model;

use app\common\model\UserBonus;
use app\common\model\MemberSet;
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
        $setModel = new MemberSet();
        $getMemberId = $setModel->getMemberId($data['amount']);
        $getMember = MemberSet::where('id',$getMemberId)->find();
        $user = $this->where('id',$data['id'])->find();
        $memberId = $user['member_id'];
        $member = MemberSet::where('id',$memberId)->find();
        $user->money = $data['amount'] + $user['money'];
        if($getMember['level'] > $member['level']){ //如果会员等级提高，则更新会员等级
            $user->member_id = $getMemberId;
        }
        $res = $user->save();
        if(!$res){
            $this->error = '充值失败';
            return false;
        }
        // 如果会员等级提升,则发生对碰
        if($getMember['level'] > $member['level']){
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



}
