<?php

namespace app\api\controller;

use app\common\model\User;
use app\common\model\UserOrder;
use think\Db;

class Orders extends Base
{


    /**
     * 奖金充值
     *
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function recharge()
    {
        if ($this->request->isPost()) {
            $model = new UserOrder();
            $data = input();
            $res = $model->recharge($data);
            return $res ? json(['code' => 200, 'msg' => '订单添加成功']) :
                json(['code' => 201, 'msg' => '订单添加失败']);
        }
        $money = Db::name('member_set')->field('id,level,money')->select();
        return json(['code' => 200, 'data' => $money]);

    }

    /**
     * 申请提现
     *
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function withdraw()
    {
        if (!$this->request->isPost()) {
            return json(['code' => 400, 'msg' => '非法请求']);
        }
        $data = input();
        $model = new UserOrder();
        $res = $model->applyWithdraw($data);
        return $res ? json(['code' => 200, 'msg' => '申请成功'])
            : json(['code' => 201, 'msg' => $model->getError()]);

    }

    /**
     * 提现或充值列表
     * type|类型(1:充值,2:提现)
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $page = input('page') ? input('page') : 1;
        $pageSize = input('page_size') ? input('page_size') : 10;
        $data = input();
        $limit = ($page - 1) * $pageSize;
        $model = new UserOrder();

        $orders = $model->where('user_id', $data['user_id'])
            ->where('type', $data['type_id'])
            ->limit($limit, $pageSize)
            ->order('id desc')
            ->select();

        return json(['code' => 200, 'data' => $orders]);

    }

    /**
     * 奖金划转
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function transfer()
    {
        $data = input();
        $data['user_id'] = $this->user['id'];
        $model = new UserOrder();
        $res = $model->transfer($data);
        return $res ? json(['code' => 200, 'msg' => '划转成功'])
            : json(['code' => 201, 'msg' => '划转失败']);
    }

    /**
     * 资金明细
     *
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function fundsDetail()
    {
        $userId = input('user_id');
        $model = new UserOrder();
        $data = $model->fundsDetail($userId);
        return json(['code' => 200, 'data' => $data]);
    }

    /**
     * 话费充值金额
     *
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function phoneList()
    {
        $list = [];
        $huafei = Db::name('system_mobile')->find();
        $content = explode('|',$huafei['content']);
        foreach ($content as $k => $v){
            $list[] = [
                'id' => $k+1,
                'money' => $v,
            ];
        }

        return json(['code' => 200, 'data' => $list]);
    }
}