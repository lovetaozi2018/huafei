<?php

namespace app\common\model;

use think\Db;
use think\Model;

class UserChild extends Model
{
    protected $table = 'hf_user_child';

    /**
     * 下级列表
     *
     * @param $uid
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function myChildren($uid)
   {
       $model = new User();
       $ids = $model->getChildren($uid);
       $ids = $ids ? $ids : [];
       $children = Db::name('user_child')
           ->where('user_id',$uid)
           ->where('child_user_id','in',$ids)
           ->field('level,count(id) as sum')
           ->group('level')
           ->select();
       foreach ($children as $k=>$v){
           $childIds = $this->getChildIds($uid,$v['level']);
           $bonus = Db::name('user')->where('id','in',$childIds)->sum('bonus');
           $children[$k]['total_bonus'] = $bonus;
           unset($childIds);
           unset($children[$k]['child_user_id']);
       }
       return ['data' => $children,'total' => count($ids)];
   }

   public function getChildIds($uid,$level=0)
   {
       $where = [];
       if($level){
           $where[] = ['level', '=', $level];
       }
       $children = $this->where('user_id',$uid)
           ->where($where)
           ->field('child_user_id')
           ->select();
       $ids = [];
       foreach ($children as $k=>$v){
           $ids[] = $v['child_user_id'];
       }

       return $ids;
   }

}