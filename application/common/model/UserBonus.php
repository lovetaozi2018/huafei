<?php

namespace app\common\model;

use think\Db;
use think\Model;

class UserBonus extends Model
{
    /**
     * 获取父级对碰奖励
     *
     * @param $userId
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function settleBonus($userId)
    {
        $childIds = $childIds1 = $childIds2 = $fathers = [];
        $model = new User();
        $fathers = $model->getFather($userId);
        $user = Db::name('user')->where('id', $userId)->find();
        $fatherId = $user['father_id'];
        // 查询父级用户所有是会员的子级,不是会员的子级不能对碰(这里不用考虑父级的会员等级，因为父级不是会员不能推广),
        $children = Db::name('user')->where('father_id', $fatherId)
            ->where('member_id', '<>', 0)
            ->select();
        foreach ($children as $k => $v) {
            $childIds1[] = $v['id'];
        }
        //查询父级下已经对碰过的会员id
        $userBonus = $this->where('user_id', $fatherId)
            ->select();
        foreach ($userBonus as $u) {
            $childIds2[] = $u['user1_id'];
            $childIds2[] = $u['user2_id'];
        }
        $childIds2 = array_unique($childIds2); //去重
        // 获取没有对碰过的子级($childIds1包含所有的子级,则两者的不同部分就是未对碰的用户)
        $childIds = array_diff($childIds1, $childIds2);
        if (empty($childIds)) {
            return [];
        }
        $childId = $childIds[0];
        $child = Db::name('user')->where('id', $childId)->find();
        $father = Db::name('user')->where('id', $fatherId)->find();
        $memberIds = [$child['member_id'], $father['member_id'], $user['member_id']];
        // 比较三者的会员等级
        $this->insertSort($memberIds);
        $minMemberId = $memberIds[0];
        foreach ($fathers as $k => $f) {
            $fathers[$k]['user_id'] = $f['father_id'];
            $fathers[$k]['member_id'] = $minMemberId;
            $fathers[$k]['from_user_id'] = $userId;
            $fathers[$k]['user1_id'] = $userId;
            $fathers[$k]['user2_id'] = $childId;
            $fathers[$k]['date'] = date('Y-m-d', time());
            $fathers[$k]['level'] = $f['level'];
            $bonusPercent = Db::name('member_bonus_percent')->where('level', $f['level'])->find(); //收益百分比
            $percent = $bonusPercent ? ($bonusPercent['percent'] / 100) : 0;
            $memberSet = Db::name('member_set')->where('id', $minMemberId)->find();
            $bonus = $memberSet['bonus']; //会员等级对碰奖金
            $amount = $bonus * $percent; //对碰奖金
            $fathers[$k]['amount'] = $amount;
        }

        return $fathers;

    }

    /**
     * 插入排序法
     *
     * @param $arr
     */
    public function insertSort(&$arr)
    {
        for ($i = 1; $i < count($arr); $i++) {
            //$insertVal是准备插入的数
            $insertVal = $arr[$i];
            //准备先和$insertIndex比较
            $insertIndex = $i - 1;
            //如果满足条件，说明插入的数比前面的数小，说明还没有找到合适位置
            while ($insertIndex >= 0 && $insertVal < $arr[$insertIndex]) {
                //把$arr[$insertIndex]这个数向后移动一位
                $arr[$insertIndex + 1] = $arr[$insertIndex];
                $insertIndex--;
            }
            //插入(这时就给$insertVal找到适当位置)
            $arr[$insertIndex + 1] = $insertVal;
        }
    }

    /**
     * 奖金记录
     *
     * @param $userId
     * @return array|false|mixed|string|\think\Collection|\think\db\Query[]|Model[]
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function bonus($userId)
    {
        $lists = [];
        $page = input('page') ? input('page') : 1;
        $pageSize = input('page_size') ? input('page_size') : 5;
//        $model = new User();
//        $ids = $model->getChildren($userId);
//        array_push($ids,$userId);

        $limit = ($page - 1) * $pageSize;
        $lists = $this->where('user_id', $userId)
            ->field('id,user_id,amount,level,created_at')
            ->order('id desc')
            ->limit($limit, $pageSize)
            ->select();
        foreach ($lists as $k => $v) {
            $lists[$k]['real_name'] = $v->user->real_name;
            unset($lists[$k]['user']);
        }

        return $lists;

    }

    public function user()
    {
        return $this->belongsTo('User', 'user_id', 'id');
    }
}