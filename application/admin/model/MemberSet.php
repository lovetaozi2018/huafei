<?php

namespace app\admin\model;

use think\Model;


class MemberSet extends Model
{
    public function adds(array $data)
    {
        if (isset($data['id']) && !empty($data['id'])) {
            $id = $data['id'];
            unset($data['id']);
            $res = $this->allowField(true)->save($data, ['id' => $id]);
        } else {
            $res = $this->allowField(true)->save($data);
        }
    }
}