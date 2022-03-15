<?php
namespace app\index\controller;

class Index
{
    public function index()
    {
        print_r(date('d-m-Y H:i:s','1643185560'));exit;
        return '<style type="text/css">*{ padding: 0; margin: 0; } div{ padding: 4px 48px;} a{color:#2E5CD5;cursor: pointer;text-decoration: none} a:hover{text-decoration:underline; } body{ background: #fff; font-family: "Century Gothic","Microsoft yahei"; color: #333;font-size:18px;} h1{ font-size: 100px; font-weight: normal; margin-bottom: 12px; } p{ line-height: 1.6em; font-size: 42px }</style><div style="padding: 24px 48px;"> <h1>:) </h1><p> ThinkPHP V5.1<br/><span style="font-size:30px">12载初心不改（2006-2018） - 你值得信赖的PHP框架</span></p></div><script type="text/javascript" src="https://tajs.qq.com/stats?sId=64890268" charset="UTF-8"></script><script type="text/javascript" src="https://e.topthink.com/Public/static/client.js"></script><think id="eab4b9f840753f8e7"></think>';
    }

    public function hello($name = 'ThinkPHP5')
    {
        return 'hello,' . $name;
    }

    public function test()
    {
              $ip = '219.134.104.255';
          $durl = 'http://ip.taobao.com/service/getIpInfo.php?ip='.$ip;
          // 初始化
          $curl = curl_init();
         // 设置url路径
         curl_setopt($curl, CURLOPT_URL, $durl);
         // 将 curl_exec()获取的信息以文件流的形式返回，而不是直接输出。
         curl_setopt($curl, CURLOPT_RETURNTRANSFER, true) ;
         // 在启用 CURLOPT_RETURNTRANSFER 时候将获取数据返回
         curl_setopt($curl, CURLOPT_BINARYTRANSFER, true) ;
         // 执行
         $data = curl_exec($curl);
         // 关闭连接
         curl_close($curl);
         // 返回数据
         print_r($data) ;



//判断访客的IP是否来自国家1或国家2
//        if(($address['data']['country'] == $verification1 || $address['data']['country'] == $verification2) && (strpos($url_ref, 'google') === false)){ //IP来自某1-2个国家，且来路URL里不含有google的
//            header("location: https://www.baidu.com");
//            exit();
//        }
    }
}
