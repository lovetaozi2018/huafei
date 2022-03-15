<?php

namespace app\admin\controller;

use app\admin\model\MemberSet;
use think\Db;


class Member extends Base
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
        $vips = Db::name('member_set')->select();
        $this->assign('vips', $vips);
        return $this->fetch();
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index1()
    {
        $param = $this->request->param();
        $pageSize = 10;
        $where = [];
        if (isset($param['keyword']) && !empty($param['keyword'])) {
            $where[] = ['title|writer|source', 'like', '%' . $param['keyword'] . '%'];
        }
        if (isset($param['type_id']) && !empty($param['type_id'])) {
            $where[] = ['type_id', '=', $param['type_id']];
        }
        $types = Db::name('newstype')->select();
        $model = new \app\common\model\News();
        $news = $model->where($where)
            ->order('id asc')
            ->paginate($pageSize);


        return view('index1', [
            'types' => $types,
            'list' => $news,
        ]);

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
        $vip = Db::name('member_set')->where('id', $id)->find();

        $this->assign('vip', $vip);
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
        $model = new MemberSet();
        if (isset($data['id']) && !empty($data['id'])) {
            $id = $data['id'];
            unset($data['id']);
            $res = $model->allowField(true)->save($data, ['id' => $id]);
        } else {
            $res = $model->allowField(true)->save($data);
        }

        return $res ? json(['code' => 200]) : json(['code' => 201]);
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
        $res = Db::name('news')->where('id',$id)->delete();
        return $res ? json(['code' => 200]) : json(['code' => 201]);
    }
}