<?php

namespace app\api\controller;

use app\common\model\Recharge;

class Recharges extends Base
{
    /**
     * 话费订单列表
     *
     */
    public function lists()
    {
        $userId = $this->user['id'];
        $model = new Recharge();
        $data = $model->lists($userId);

        return json(['code' => 200,'data' => $data]);
    }
}