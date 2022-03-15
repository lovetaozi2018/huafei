<?php


namespace app\admin\model;

use think\Db;
use think\Model;

class MemberBonusPercent extends Model
{
    protected $table ='hf_member_bonus_percent';

    /**
     * 新增或编辑
     * @param array $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function addPercent(array $data)
    {
        if (isset($data['id']) && !empty($data['id'])) {
            $id = $data['id'];
            $bonus = Db::name('member_bonus_percent')
                ->where('level',$data['level'])
                ->where('id','<>',$id)
                ->find();
            if($bonus){
                $this->error = '该子级层级已存在';
                return false;
            }
            unset($data['id']);
            $res = $this->allowField(true)->save($data, ['id' => $id]);
        } else {
            $bonus = Db::name('member_bonus_percent')->where('level',$data['level'])->find();
            if($bonus){
                $this->error = '该子级层级已存在';
                return false;
            }
            $res = $this->allowField(true)->save($data);
        }
        if(!$res){
            $this->error = '保存失败';
            return false;
        }

        return true;
    }
}