<?php
namespace app\admin\controller;

use app\admin\model\Admin;
use app\admin\model\SystemBonus;
use app\admin\model\SystemMobile;
use app\admin\model\SystemPromote;
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
    
     /**
     * 推广设置
     *
     * @return false|mixed|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function promote()
    {
        if($this->request->isAjax()){
            $data = input();
            $promote = SystemPromote::find($data['id']);
            $promote->content = $data['content'];
            $res = $promote->save();
            return $res ? json(['code' => 200]) : json(['code'=>201]);

        }
        $promote = Db::name('system_promote')->find();
        $this->assign('promote', $promote);
        return $this->fetch();

    }

    /**
     * 话费金额设置
     *
     * @return false|mixed|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function bonus()
    {
        if($this->request->isAjax()){
            $data = input();
            $bonus = SystemBonus::find($data['id']);
            $bonus->content = $data['content'];
            $res = $bonus->save();
            return $res ? json(['code' => 200]) : json(['code'=>201]);
        }
        $bonus = Db::name('system_bonus')->find();
        $this->assign('bonus', $bonus);
        return $this->fetch();

    }


    /**
     * 话费金额设置
     *
     * @return false|mixed|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function huafei()
    {
        if($this->request->isAjax()){
            $data = input();
            $promote = SystemMobile::find($data['id']);
            $promote->content = $data['content'];
            $res = $promote->save();
            return $res ? json(['code' => 200]) : json(['code'=>201]);

        }
        $huafei = Db::name('system_mobile')->find();
        $this->assign('huafei', $huafei);
        return $this->fetch();

    }




}