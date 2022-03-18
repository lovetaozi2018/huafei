<?php

namespace app\admin\controller;

use app\admin\model\UserOrder;
use think\Db;


class Orders extends Base
{
    /**
     * 充值或提现列表
     *  type_id|1:充值，2:提现
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $typeId = $this->request->param('type_id');
        $model = new UserOrder();
        $orders =$model->where('type',$typeId)
            ->select();
        foreach ($orders as $k=>$u){
            $orders[$k]['real_name'] = $u->user->real_name;
            unset($u['user']);
        }
        $this->assign('orders', $orders);
        $this->assign('type', $typeId);
        return $this->fetch();
    }

    /**
     * 手动充值
     *
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function recharge()
    {
        $orderId = input('id');
        $model = new UserOrder();
        $res = $model->addRecharge($orderId);
        return $res ? json(['code' => 200]) :
            json(['code' => 201,'msg' => $model->getError()]);
    }

    /**
     * 删除
     *
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function delete()
    {
        $id = $this->request->param('id');
        $res = Db::name('user_order')->where('id',$id)->delete();
        return $res ? json(['code' => 200]) : json(['code' => 201]);
    }


}