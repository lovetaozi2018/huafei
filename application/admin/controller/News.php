<?php

namespace app\admin\controller;

use think\Db;


class News extends Base
{
    /**
     * 新闻列表
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $news = Db::name('news')->select();
        $this->assign('news', $news);
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
        $types = Db::name('newstype')->select();
        return view('edit', [
            'types' => $types,
        ]);
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
//        if ($this->request->isPost()) {
//            $this->success('保存成功!', cookie('__forward__'));
//        }
        $new = Db::name('news')->where('id', $id)->find();
        if (empty($new)) {
            $this->error('数据不存在!');
        }
        $types = Db::name('newstype')->select();
        $this->assign('new', $new);
        $this->assign('content', json_encode($new['content']));
        $this->assign('types', $types);
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
        $data = $this->request->param();
        if ($data['id']) {
            $id = $data['id'];
            unset($data['id']);
            $res = Db::name('news')->where('id', $id)->update($data);
        } else {
            $res = Db::name('news')->insert($data);
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