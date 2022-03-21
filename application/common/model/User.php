<?php

namespace app\common\model;

use Firebase\JWT\ExpiredException;
use Firebase\JWT\Key;
use think\Db;
use think\facade\Cache;
use think\Model;
use Firebase\JWT\JWT;


class User extends Model
{
    protected $jwt_secret_key = '123abc';
    protected $expire_time = 24 * 60 * 60 * 7;
    protected $hidden = ['password'];

    public function createToken($user, $exptime = 0)
    {
        $key = md5($this->jwt_secret_key);//jwt签发秘钥
        $time = time(); //签发时间
        $expire = $time + $exptime; //过期时间
        $token = [
            'iss' => '',
            'aud' => '',
            'iat' => $time, //签发时间
            'nbf' => $time, //立即生效
            'exp' => $expire,
            'id' => $user['id']
        ];
        $jwt = JWT::encode($token, $key, 'HS256');

        return $jwt;
    }

    //验证token是否合法
    function verifyToken($token)
    {
        $key = md5($this->jwt_secret_key);//jwt签发秘钥
        try {
            $auth = JWT::decode($token, new Key($key, 'HS256'));
            $id = $auth->id;
            $user = $this->where('id', $id)->find()->toArray();
            return ['code' => 200, 'user' => $user];
        } catch (ExpiredException $e) {
            return ['code' => 201, 'msg' => 'token过期'];
        } catch (\Exception $e) {
            return ['code' => 202, 'msg' => 'token错误'];
        }
    }

    /**
     * 注册
     *
     * @param array $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function register(array $data)
    {
        $this->startTrans();
        $code = Cache::get('register_' . $data['phone']);
        $fatherId = 0;
        $validate = new \app\api\validate\User();
        if (!$validate->scene('register')->check($data)) {
            $this->error = $validate->getError();
            return false;
        }

        if ($data['code'] != $code) {
            $this->error = '验证码错误';
            return false;
        }
        $post = [
            'phone' => $data['phone'],
            'password' => md5_pass($data['password']),
            'reg_ip' => get_ip(),
        ];
        if (isset($data['father_id']) && !empty($data['father_id'])) {
            $fatherId = $data['father_id'];
            $post['father_id'] = $fatherId;
        }
        $id = Db::name('user')->insertGetId($post);
        $user = $this->where('id', $id)->find();
        $user->token = $this->createToken($user, $this->expire_time);
        $res = $user->save();
        if (!$res) {
            $this->error = '注册失败';
            $this->rollback();
            return false;
        }

        if ($fatherId) {
            $rows = [];
            $fatherIds = $this->getFather($fatherId);
            if (sizeof($fatherIds) != 0) {
                foreach ($fatherIds as $k => $v) {
                    $father = $this->where('id', $v['father_id'])->find();
                    $rows[] = [
                        'user_id' => $father['id'],
                        'child_user_id' => $id,
                        'level' => $v['level'],
                        'member_id' => $father['member_id'],
                        'child_member_id' => 0,
                    ];
                }
                Db::name('user_child')->insertAll($rows);
            }
        }

        $this->commit();
        return true;
    }


    /**
     * 登录
     *
     * @param $post
     * @return array|\think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function login($post)
    {
        $user = $this->where('phone', $post['phone'])->find();
        if (!$user) {
            return json(['code' => 201, 'msg' => '手机号码不正确']);
        }
        if (md5_pass($post['password']) != $user['password']) {
            return json(['code' => 201, 'msg' => '密码错误']);
        }
        if($user['status'] == 0){
            return json(['code' => 201, 'msg' => '用户已禁止登录']);
        }
        $token = $this->createToken($user,24*60*60*7);
        $user->last_login_time = date('Y-m-d H:i:s',time());
        $user->reg_ip = get_ip();
        $user->token = $token;
        $res = $user->save();
        return $res ? ['code' => 200, 'user' => $user] : ['code' => 202, 'msg' => '登录失败'];
    }

    /**
     * 重置密码
     *
     * @param array $post
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function modifyPass(array $post)
    {
        $code = Cache::get('modify_' . $post['phone']);
        if ($post['code'] != $code) {
            return ['code' => 201, 'msg' => '验证码错误'];
        }
        $user = $this->where('phone', $post['phone'])->find();
        $password = md5_pass($post['password']);
        $user->password = $password;
        $user->token = $this->createToken($user, $this->expire_time);
        $res = $user->save();
        return $res ? ['code' => 200, 'msg' => '修改成功', 'user' => $user] :
            ['code' => 202, 'msg' => '修改失败'];

    }

    /**
     * 上传头像
     *
     * @param $file
     * @param string $filePath
     * @return array
     */
    public function uploadImg($file,$filePath='')
    {
        $apiPath =str_replace('\\','/',env('root_path'))  . 'public';
        $filePath = $filePath ? $filePath : '/uploads/images';
        // 去除空格
        $name = str_replace(' ', '', $file['name']);
        //删除特殊字符
        $name = preg_replace('/[\x{10000}-\x{10FFFF}]/u', '', $name);
        $original = $file['tmp_name'];
        $ext = explode('.', $name);//后缀
        $ext = strtolower(array_pop($ext)); // 后缀转化成小写
        if (!file_exists($apiPath.$filePath)) {
            $res = mkdir($apiPath.$filePath, 0777, true);
            if (!$res) {
                return ['code' => '201', 'msg' => '文件夹创建失败'];
            }
        }
        $fileName = $filePath . '/' . md5(microtime(true)) . '.' . $ext;

        if (!move_uploaded_file($original, $apiPath . $fileName)) {
            return ['code' => '202', 'msg' => '上传失败'];
        }
        return ['code' => '200', 'path' => $fileName];

    }

    /**
     * 编辑用户(头像、昵称、性别、简介)
     *
     * @param array $data
     * @return array|bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function userEdit(array $data)
    {
        $user = $this->where('id', $data['user_id'])->find();
        $file = $_FILES;
        // 头像
        if (isset($file['head_img']) && !empty($file['head_img'])) {
            $res = $this->uploadImg($file['head_img']);
            if ($res['code'] != 200) {
                $this->error = $res['msg'];
                return false;
            }
            $headImg = $res['path'];
            $user->head_img = $headImg;
        }
        // 性别
        if (isset($data['sex']) && !empty($data['sex'])) {
            $user->sex = $data['sex'];
        }
        // 昵称
        if (isset($data['username']) && !empty($data['username'])) {
            $user->sex = $data['sex'];
        }
        // 简介
        if (isset($data['remark']) && !empty($data['remark'])) {
            $user->remark = $data['remark'];
        }

        $res = $user->save();
        if (!$res) {
            $this->error = '修改失败';
            return false;
        }
        return true;
    }

    /**
     * 修改密码
     *
     * @param array $post
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function modifyPassword(array $post)
    {
        $user = $this->where('id', $post['user_id'])->find();
        if (md5_pass($post['old_password']) != $user['password']) {
            $this->error = '原密码错误';
            return false;
        }

        $password = md5_pass($post['password']);
        $user->password = $password;
        $res = $user->save();
        if (!$res) {
            $this->error = '修改失败';
            return false;
        }
        return true;
    }

    /**
     * 实名认证
     *
     * @param array $post
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function identity(array $post)
    {
        $user = $this->where('id', $post['user_id'])->find();
        $user->id_card = $post['id_card'];
        $user->real_name = $post['real_name'];
        $res = $user->save();
        return $res ? true : false;
    }

    /**
     * 获取用户的所有上级
     *
     */
    function getFather($uid, $fatherIds = [])
    {
        static $level = 0;
        $user = Db::name('user')->where(['id' => $uid])->find();
        if ($user && $user['father_id']) {
            $level++;
            $fatherIds[] = [
                'father_id' => $user['father_id'],
                'level' => $level
            ];
            return $this->getFather($user['father_id'], $fatherIds);
        }

        return $fatherIds;
    }

    /**
     * 获取用户所有的子级id
     *
     */
    public function getChildren($uid, $childIds = [])
    {
        global $childIds;
        $childrens = Db::name('user')->where(['father_id' => $uid])->select();
        if ($childrens) {
            foreach ($childrens as $d) {
                $childIds[] = $d['id'];
                $this->getChildren($d['id'],$childIds);
            }
        }
        return $childIds;
    }

    /**
     * 团队明细
     *
     * @param $userId
     * @return array
     */
    public function memberDetails($userId)
    {
        $ids = $this->getChildren($userId);
        array_push($ids,$userId);
        $totalBonus = Db::name('user')->where('id','in',$ids)->sum('bonus'); //总奖金
        //今日奖金
        $date = date('Y-m-d',time());
        $bonus = Db::name('user_bonus')->where('user_id','in',$ids)
            ->where('date',$date)
            ->sum('bonus');
        $url = '?father_id='.$userId;

        $data = [
            'total_bonus' => $totalBonus,
            'bonus' => $bonus,
            'url' => $url
        ];

        return $data;

    }

}