<?php

namespace app\admin\controller;

use app\admin\model\MemberBonusPercent;
use think\Db;


class MemberBonus extends Base
{

    /**
     * 对碰奖励设置列表
     * @return mixed|string|void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $memberBonus = Db::name('member_bonus_percent')->select();
        $this->assign('memberBonus', $memberBonus);
        return $this->fetch();
    }


    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function add()
    {
        return view('edit');
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function edit()
    {
        $id = input('id');
        $memberBonus = Db::name('member_bonus_percent')->where('id', $id)->find();
        $this->assign('memberBonus', $memberBonus);
        return view('edit');
    }

    /**
     * 新增或更新
     *
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function save()
    {
        $data = input();
        $model = new MemberBonusPercent();
        $res = $model->addPercent($data);

        return $res ? json(['code' => 200]) : json(['code' => 201,'msg' => $model->getError()]);
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
        $res = Db::name('member_bonus_percent')->where('id',$id)->delete();
        return $res ? json(['code' => 200]) : json(['code' => 201]);
    }
}