<?php

namespace app\admin\controller;

use app\admin\model\MemberSet;
use app\common\model\MobileCoupons;
use think\Db;


class Coupons extends Base
{

    /**
     * 会员设置列表
     * @return mixed|string|void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $coupons = Db::name('mobile_coupons')->select();
        $this->assign('coupons', $coupons);
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
        $coupon = Db::name('mobile_coupons')->where('id', $id)->find();

        $this->assign('coupon', $coupon);
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
        $model = new MobileCoupons();
        $res = $model->adds($data);

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
        $res = Db::name('mobile_coupons')->where('id',$id)->delete();
        return $res ? json(['code' => 200]) : json(['code' => 201]);
    }
}