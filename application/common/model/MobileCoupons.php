<?php

namespace app\common\model;

use think\Model;

class MobileCoupons extends Model
{
    /**
     * @param array $post
     * @return bool
     */
    public function adds(array $post)
    {
        $file = $_FILES['logo'];
        if($file){
            $filePath = '/uploads/images/mobile';
            $model = new User();
            $result = $model->uploadImg($file,$filePath);
            if($result['code'] !=200){
                $this->error = '图片上传失败';
                return false;
            }
            $post['logo'] = $result['path'];
        }

        if (isset($post['id']) && !empty($post['id'])) {
            $id = $post['id'];
            unset($post['id']);
            $res = $this->allowField(true)->save($post, ['id' => $id]);
        } else {
            $res = $this->allowField(true)->save($post);
        }
        if(!$res){
            $this->error = '保存失败';
            return false;
        }

        return  true;

    }
}