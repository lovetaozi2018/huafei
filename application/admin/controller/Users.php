<?php

namespace app\admin\controller;

use app\admin\model\User;
use think\Db;

class Users extends Base
{
    /**
     * 列表
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $users = Db::name('user')
            ->select();

        $this->assign('users', $users);
        return $this->fetch();
    }

    public function index1()
    {
        $param = $this->request->param();
        $pageSize = 2;
        $where = [];
        if (isset($param['keyword']) && !empty($param['keyword'])) {
            $where[] = ['title|writer|source', 'like', '%' . $param['keyword'] . '%'];
        }
        if (isset($param['type_id']) && !empty($param['type_id'])) {
            $where[] = ['type_id', '=', $param['type_id']];
        }
        $model = new User();
        $users = $model->where($where)
            ->order('id asc')
            ->paginate($pageSize);


        return view('index1', [
            'users' => $users,
        ]);
    }

    public function recharge($id)
    {
        $this->assign('id', $id);
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
    public function addRecharge()
    {
        $data = input();
        $user = new User();
        $res = $user->addRecharge($data);
        return $res ? json(['code' => 200]) : json(['code' => 201]);
    }

    public function members()
    {
        $userId = $this->request->param('id');
        $model = new User();
        $data = $model->members($userId);

        $this->assign('members', $data['members']);
        $this->assign('childTotal', $data['total_child']);
        $this->assign('totalBonus', $data['total_bonus']);
        $this->assign('totalMoney', $data['total_money']);
        return $this->fetch();
    }

    public function code()
    {
        $users = Db::name('user')
            ->select();

        $this->assign('users', $users);
        return $this->fetch();
    }


}