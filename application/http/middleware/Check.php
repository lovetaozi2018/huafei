<?php

namespace app\http\middleware;

use app\common\model\User;

class Check
{
    public function handle($request, \Closure $next)
    {
        $token = $request->header('token');
        $model = new User();
        $res = $model->verifyToken($token);
        if($res['code'] !=200){
            return json(['code' => $res['code'],'msg' => $res['msg']]);
        }
        $request->user_id = $res['user']['id'];
        return $next($request);
    }
}
