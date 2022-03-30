<?php

namespace app\admin\model;

use think\Model;

class MobileRecharge extends Model
{
    protected $table='hf_mobile_recharge';

    public function adds($orderId)
    {
        $this->startTrans();
        $order = $this->where('id',$orderId)->find();
        $order->status = 1;
        if(!$order->save()){
            $this->error = '话费充值失败';
            $this->rollback();
            return false;
        }

        $this->commit();
        return true;
    }
}
