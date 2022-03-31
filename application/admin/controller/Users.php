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
        foreach ($users as $k => $u){
            $father = Db::name('user')->where('id',$u['father_id'])->find();
            $users[$k]['father'] = $father ? ($father['real_name'] ? $father['real_name'] : $father['username']) : '无';
        }

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

    public function detail($id)
    {
        $user = Db::name('user')->where('id',$id)->find();
        $father = Db::name('user')->where('id',$user['father_id'])->find();
        $user['father'] = $father ? ($father['real_name'] ? $father['real_name'] : $father['username']) : '无';
        $this->assign('user', $user);
        return $this->fetch('details');
    }

    /**
     * 收款码
     *
     * @return false|mixed|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function code()
    {
        if($this->request->isPost()){
            $id = input('id');
            $model = new User();
            $files = $_FILES;
            $res = $model->codeEdit($files,$id);
            return $res ? json(['code' => 200,'msg' => '上传成功']) :
                json(['code' => 201,'msg' => $model->getError()]);
        }
        $users = Db::name('user')
            ->select();
        $this->assign('users', $users);
        return $this->fetch();
    }

    public function codeEdit($id)
    {
        $user = Db::name('user')->where('id',$id)->find();
        $this->assign('user', $user);
        return $this->fetch('code_edit');
    }

    /**
     * 充值界面
     *
     * @param $id
     * @return false|mixed|string
     */
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

    public function edit($id)
    {
        $user = Db::name('user')->where('id',$id)->find();

        $this->assign('user', $user);
        return $this->fetch();
    }

    /**
     * 重置用户密码
     *
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function reset()
    {
        $id = input('id');
        $model = new User();
        $user = $model->where('id',$id)->find();
        $user->password = md5_pass('123456');
        $res = $user->save();
        return $res ? json(['code' => 200]) : json(['code' => 201]);
    }

    /**
     * 启用/禁用用户
     * $type:1:启用,2:禁用
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function power()
    {
        $id = input('id');
        $type = input('type');
        $model = new User();
        $user = $model->where('id',$id)->find();
        $user->status = $type == 1 ? 1 : 0;
        $res = $user->save();
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




}