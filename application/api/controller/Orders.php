<?php

namespace app\api\controller;

use app\common\model\UserOrder;
use think\Db;

class Orders extends Base
{
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
        return $res ? json(['code' => 200,'msg' => '申请成功'])
            : json(['code' => 201,'msg' => $model->getError()]);

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

        $orders = $model->where('user_id',$data['user_id'])
            ->where('type',$data['type_id'])
            ->limit($limit,$pageSize)
            ->order('id desc')
            ->select();
        foreach ($orders as $k => $v){
            if($v['status'] == 1){
                $orders[$k]['status'] = '成功';
            }elseif($v['status'] == 2){
                $orders[$k]['status'] = '失败';
            }else{
                $orders[$k]['status'] = '申请中';
            }
        }

        return json(['code' => 200,'data' => $orders]);

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
        $model = new UserOrder();
        $res = $model->transfer($data);
        return $res ? json(['code' => 200,'msg' => '划转成功'])
            : json(['code' => 201,'msg' => '划转失败']);
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
        $page = input('page') ? input('page') : 1;
        $pageSize = input('page_size') ? input('page_size') : 10;
        $limit = ($page - 1) * $pageSize;
        $model = new UserOrder();
        $orders = $model->where('user_id',$userId)
            ->limit($limit,$pageSize)
            ->order('id desc')
            ->select();

        return json(['code' => 200,'data' => $orders]);
    }
}