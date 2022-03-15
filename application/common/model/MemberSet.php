<?php

namespace app\common\model;

use think\Db;
use think\Model;

class MemberSet extends Model
{
    public function getMemberId($amount)
    {
        $memberId = 0;
        if($amount >= MemberSet::set(1)['amount'] && $amount < MemberSet::set(2)['amount']){
            $memberId = 1;
        }
        if($amount >= MemberSet::set(2)['amount'] && $amount < MemberSet::set(3)['amount']){
            $memberId = 2;
        }
        if($amount >= MemberSet::set(3)['amount'] && $amount < MemberSet::set(4)['amount']){
            $memberId = 3;
        }
        if($amount >= MemberSet::set(4)['amount'] && $amount < MemberSet::set(5)['amount']){
            $memberId = 4;
        }
        if($amount >= MemberSet::set(5)['amount'] && $amount < MemberSet::set(6)['amount']){
            $memberId = 5;
        }

        if($amount >= MemberSet::set(6)['amount'] ){
            $memberId = 6;
        }

        return $memberId;

    }

    public static function set($id)
    {
        return Db::name('member_set')->where('id',$id)->find();
    }
}