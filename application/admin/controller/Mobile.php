<?php

namespace app\admin\controller;

use app\admin\model\MobileRecharge;
use app\common\model\Recharge;


class Mobile extends Base
{
    /**
     * 话费充值订单列表
     *
     *  type_id|1:充值，2:提现
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index1()
    {
        $model = new Recharge();
        $orders = $model->order('id desc')->select();
        foreach ($orders as $k=>$v){
            $realName = $v->user->real_name;
            $v['real_name'] = $realName;
        }

        $this->assign('orders', $orders);
        return $this->fetch();
    }

    public function index()
    {
        $param = $this->request->param();
        $where = [];
        if (isset($param['real_name']) && !empty($param['real_name'])) {
            $where[] = ['b.real_name', 'like', '%' . $param['real_name'] . '%'];
        }
        if (isset($param['phone']) && !empty($param['phone'])) {
            $where[] = ['a.phone', 'like', '%' . $param['phone'] . '%'];
        }
        if (isset($param['order_no']) && !empty($param['order_no'])) {
            $where[] = ['a.order_no', 'like', '%' . $param['order_no'] . '%'];
        }
        if (isset($param['status'])) {
            if($param['status'] == 3){
                $where[] = ['a.status', 'in', [0,1,2]];
            }else{
                $where[] = ['a.status', '=', $param['status']];
            }
        }
        if (isset($param['start_date']) && !empty($param['start_date'])) {
            $where[] = ['a.date', '>=', $param['start_date']];
        }

        if (isset($param['end_date']) && !empty($param['end_date'])) {
            $where[] = ['a.date', '<=', $param['end_date']];
        }
        $model = new Recharge();
        $orders = $model->alias('a')
            ->field('a.*,b.real_name')
            ->join('user b','a.user_id=b.id')
            ->where($where)
            ->order('a.id desc')
            ->paginate();

        $orders->appends($param);

        foreach ($orders as $k=>$v){
            $realName = $v->user->real_name;
            $v['real_name'] = $realName;
        }

        return view('index1', [
            'orders' => $orders,
        ]);

    }

    /**
     * 话费手动充值
     *
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function recharge()
    {
        $orderId = input('id');
        $model = new MobileRecharge();
        $res = $model->adds($orderId);
        return $res ? json(['code' => 200]) :
            json(['code' => 201,'msg' => $model->getError()]);
    }

    public function delete()
    {
        $id = input('id');
        $model = new Recharge();
        $order = $model->where('id',$id)->find();
        $res = $order->delete();

        return $res ? json(['code' => 200]) : json(['code' => 201]);
    }


}