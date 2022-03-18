<?php
namespace app\admin\controller;

use app\admin\model\Admin;
use think\Db;

class Admins extends Base
{
    /**
     * 话费充值订单列表
     *
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $model = new Admin();
        $admins = $model->order('id desc')->select();

        $this->assign('admins', $admins);
        return $this->fetch();
    }

    public function logs()
    {
        $logs = Db::name('admin_log')->order('id desc')->select();

        $this->assign('logs', $logs);
        return $this->fetch();
    }

    public function edit()
    {
        return $this->fetch();
    }

    /**
     * 修改管理员密码
     *
     * @return \think\response\Json
     */
    public function modifyPwd()
    {
        $data = input();
        $model = new Admin();
        $res = $model->modifyPassword($data);

        return $res ? json(['code' => 200]) : json(['code'=>201,'msg' =>$model->getError()]);
    }




}