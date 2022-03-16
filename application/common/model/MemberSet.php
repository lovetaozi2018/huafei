<?php

namespace app\common\model;

use think\Db;
use think\Model;

class MemberSet extends Model
{
    /**
     * 根据充值金额查询对应会员等级
     *
     * @param $amount
     * @return int|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getMemberId($amount)
    {
        $memberId = 0;
        $maxAmount = $this->where('amount', '>', $amount)->min('amount');
        if($maxAmount){
            $memberSet = $this->where('amount',$maxAmount)->find();
            $minAmount = $this->where('amount', '<', $memberSet['amount'])->max('amount');
            if(!$minAmount){
                // 如果$minAmount不存在，则说明$memberSet是最小的会员等级,则说明充值金额达不到最低会员等级
                $memberId = 0;
            }else{
                $set = $this->where('amount', $minAmount)->find();
                $memberId = $set['id'];
            }
        }else{
            //如果$maxAmount不存在,则说明$amount已经超过最大会员等级设定金额
            $set = $this->where('amount','<',$amount)->order('id desc')->find();
            $memberId = $set['id'];
        }

        return $memberId;

    }

    public static function set($id)
    {
        return Db::name('member_set')->where('id',$id)->find();
    }
}