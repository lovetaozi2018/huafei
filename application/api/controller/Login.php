<?php

namespace app\api\controller;

use app\common\model\User;
use think\Controller;
use think\Db;
use think\facade\Cache;

class Login extends Controller
{

//    protected $middleware = [
//        'check' => [
//            'except' => ['login']
//        ], //jwt中间件 验证用户登录信息
//    ];


    /**
     * 发送注册验证码
     *
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function sendSms()
    {
        if (!$this->request->isPost()) {
            return json(['code' => 400, 'msg' => '非法请求']);
        }
        $phone = input('phone');
        $user = Db::name('user')->where('phone', $phone)->find();
        if ($user) {
            return json(['code' => 201, 'msg' => '该手机号码已注册']);
        }
        $code = rand(1000, 9999);
        $str = '你的注册验证码为:' . $code . ',30分钟内有效。';
        $re = send_sms($phone, $str);
        if ($re) {
            Cache::set('register_' . $phone, $code, 1800);
            return json(['code' => 200, 'msg' => '验证码发送成功']);
        } else {
            return json(['code' => 202, 'msg' => '验证码发送失败']);
        }

    }

    /**
     * 注册
     *
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function register()
    {
        if (!$this->request->isPost()) {
            return json(['code' => 400, 'msg' => '非法请求']);
        }
        $model = new User();
        $data = input();
        $res = $model->register($data);
        if (!$res) {
            return json(['code' => 201, 'msg' => $model->getError()]);
        }
        return json(['code' => 200, 'msg' => '注册成功']);

    }

    /**
     * 登录
     *
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function login()
    {
        if (!$this->request->isPost()) {
            return json(['code' => 400, 'msg' => '非法请求']);
        }
        $data = input();
        $model = new User();
        $user = $model->where('phone', $data['username'])->find();
        if (!$user) {
            return json(['code' => 201, 'msg' => '用户名不正确']);
        }
        if (md5_pass($data['password']) != $user['password']) {
            return json(['code' => 201, 'msg' => '密码错误']);
        }
        if($user['status'] == 0){
            return json(['code' => 201, 'msg' => '用户已禁止登录']);
        }
        $token = $model->createToken($user,24*60*60*7);
        $user->token = $token;
        $res = $user->save();
        return $res ? json(['code' => 200, 'user' => $user, 'token' => $token]) :
            json(['code' => 202, 'msg' => '登录失败']);

    }

    public function mdySendSms()
    {
        if (!$this->request->isPost()) {
            return json(['code' => 400, 'msg' => '非法请求']);
        }
        $phone = input('phone');
        $user = Db::name('user')->where('phone', $phone)->find();
        if(!$user){
            return json(['code' => 201, 'msg' => '用户不存在']);
        }
        $code = rand(1000, 9999);
        $str = '你正在重置密码,验证码为:' . $code . ',30分钟内有效。';
        $re = send_sms($phone, $str);
        if ($re) {
            Cache::set('modify_' . $phone, $code, 1800);
            return json(['code' => 200, 'msg' => '验证码发送成功']);
        } else {
            return json(['code' => 202, 'msg' => '验证码发送失败']);
        }
    }

    /**
     * 重置密码
     *
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function mdyPass()
    {
        if (!$this->request->isPost()) {
            return json(['code' => 400, 'msg' => '非法请求']);
        }
        $post = input();
        $model = new User();
        $res = $model->modifyPass($post);
        return json($res);

    }


}