<?php

// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件


/**
 * 接口操作成功
 * @param array $data
 * @param string $message
 * @param int $code
 * @return \think\response\Json
 */
function outputSuccess($data = [], $message = 'ok', $code = 1)
{
    $r = [
        'code' => $code,
        'msg' => $message,
        'data' => $data
    ];
    return json($r);
}


/**
 * 接口操作失败
 * @param string $message
 * @param int $code
 * @return \think\response\Json
 */
function outputError($message = '操作失败', $code = -1)
{
    $r = [
        'code' => $code,
        'msg' => $message,
    ];
    return json($r);
}


/**
 * 输出错误
 * @param string $message
 * @param int $code
 */
function showError($message = '操作失败', $code = -1)
{
    header('Content-type:text/json');
    die(json_encode(['code' => $code, 'msg' => $message], JSON_UNESCAPED_UNICODE));
}


/**
 * 二维数组比较
 * @param $array1 : 已此数组为基准
 * @param $array2
 * @return array
 */
function array_diff_assoc2_deep($array1, $array2)
{
    $ret = array();
    foreach ($array1 as $k => $v) {
        if (!isset($array2[$k])) $ret[$k] = $v;
        else if (is_array($v) && is_array($array2[$k])) $ret[$k] = array_diff_assoc2_deep($v, $array2[$k]);
        else if ($v != $array2[$k]) $ret[$k] = $v;
        else {
            unset($array1[$k]);
        }

    }
    return array_filter($ret);
}


/**
 * 手机号中间变星星
 * @param $phone
 * @return mixed
 */
function phoneToStars($phone)
{
    return substr_replace((string)$phone, '****', 3, 4);
}
/**
 * 生成一个唯一的订单号
 * @return string
 */
function getOrderid()
{
    $yCode = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
    $orderSn = $yCode[intval(date('Y')) - 2018] . strtoupper(dechex(date('m'))) . date('d') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf('%02d', rand(0, 99));
    return $orderSn;
}


/**
 * 计算利息
 * @param $money 本金
 * @param $day 贷款时间
 * @param $interest_per_diem 日息
 * @return string
 */
function calculatedInterest($money,$day,$interest_per_diem)
{
    $rixi = (floatval($interest_per_diem)/1000);
    return floatval($money) * $rixi * (int)$day;
}


/**
 * 两位小数
 * @param $number
 * @param $digit
 * @return string
 */
function decimal($number, $digit = 2)
{
    return sprintf("%1\$.{$digit}f", $number);
}


/**
 * 格式化时间
 * @param $time
 * @param int $type : 0年-月-日 时-分-秒   1年-月-日   2年月日时分秒  3年月日
 * @param string $f
 * @return false|string
 */
function formatTime($time, $type = 0, $f = '-')
{
    $arr = [
        "Y{$f}m{$f}d H:i:s",
        "Y{$f}m{$f}d",
        'YmdHis',
        'Ymd',
    ];
    return date($arr[$type], $time);
}




/**
 * XXTEA加密/解密算法
 *
 * @param $str : 原始字符串
 * @param $action : 操作类型是加密还是解密('ENCODE'
 *            OR 'DECODE', 默认为'DECODE')
 * @param $key : 加密/解密的密钥
 * @return string
 * @copyright http://coolcode.org/?action=show&id=128
 */
function xxtea_crypt($str, $action = 'DECODE', $key = '')
{
    if (empty ($str)) {
        return '';
    }

    $key = empty ($key) ? $GLOBALS ['authkey'] : $key;
    $str = $action == 'DECODE' ? base64_decode($str) : $str;
    $v = str2long($str, $action == 'DECODE' ? false : true);
    $k = str2long($key, false);

    if (empty ($v) || empty ($k)) {
        return '';
    }

    $len = count($k);

    if ($len < 4) {
        for ($i = $len; $i < 4; $i++) {
            $k [$i] = 0;
        }
    }

    $n = count($v) - 1;
    $z = $v [$n];
    $y = $v [0];
    $delta = 0x9E3779B9;
    $q = floor(6 + 52 / ($n + 1));

    if ($action == 'DECODE') {
        $sum = int32($q * $delta);
        while ($sum != 0) {
            $e = $sum >> 2 & 3;
            for ($p = $n; $p > 0; $p--) {
                $z = $v [$p - 1];
                $mx = int32((($z >> 5 & 0x07ffffff) ^ $y << 2) + (($y >> 3 & 0x1fffffff) ^ $z << 4)) ^ int32(($sum ^ $y) + ($k [$p & 3 ^ $e] ^ $z));
                $y = $v [$p] = int32($v [$p] - $mx);
            }
            $z = $v [$n];
            $mx = int32((($z >> 5 & 0x07ffffff) ^ $y << 2) + (($y >> 3 & 0x1fffffff) ^ $z << 4)) ^ int32(($sum ^ $y) + ($k [$p & 3 ^ $e] ^ $z));
            $y = $v [0] = int32($v [0] - $mx);
            $sum = int32($sum - $delta);
        }
        return long2str($v, true);
    } else {
        $sum = 0;
        while (0 < $q--) {
            $sum = int32($sum + $delta);
            $e = $sum >> 2 & 3;
            for ($p = 0; $p < $n; $p++) {
                $y = $v [$p + 1];
                $mx = int32((($z >> 5 & 0x07ffffff) ^ $y << 2) + (($y >> 3 & 0x1fffffff) ^ $z << 4)) ^ int32(($sum ^ $y) + ($k [$p & 3 ^ $e] ^ $z));
                $z = $v [$p] = int32($v [$p] + $mx);
            }
            $y = $v [0];
            $mx = int32((($z >> 5 & 0x07ffffff) ^ $y << 2) + (($y >> 3 & 0x1fffffff) ^ $z << 4)) ^ int32(($sum ^ $y) + ($k [$p & 3 ^ $e] ^ $z));
            $z = $v [$n] = int32($v [$n] + $mx);
        }
        return base64_encode(long2str($v, false));
    }
}

function long2str($v, $w)
{
    $len = count($v);
    $n = ($len - 1) << 2;
    if ($w) {
        $m = $v [$len - 1];
        if (($m < $n - 3) || ($m > $n)) {
            return false;
        }
        $n = $m;
    }
    $s = array();
    for ($i = 0; $i < $len; $i++) {
        $s [$i] = pack("V", $v [$i]);
    }

    return $w ? substr(implode('', $s), 0, $n) : implode('', $s);
}

function str2long($s, $w)
{
    $v = unpack("V*", $s . str_repeat("\0", (4 - strlen($s) % 4) & 3));
    $v = array_values($v);
    if ($w) {
        $v [count($v)] = strlen($s);
    }
    return $v;
}

function int32($n)
{
    while ($n >= 2147483648)
        $n -= 4294967296;
    while ($n <= -2147483649)
        $n += 4294967296;
    return ( int )$n;
}



function tree($data, $pid,$id_field='id',$pid_field='pid')
{
    $rows = [];
    foreach ($data as $row) {
        if ($row[$pid_field] == $pid) {
            $row['children'] = tree($data, $row[$id_field]);
            if (empty($row['children'])) {
                $row['children'] = '';
            }
            $rows[] = $row;
        }

    }
    return $rows;
}


/**
 * 通过$delimiter 对 $str 进行分割, 返回数组
 * @param $str
 * @param string $delimiter
 * @return array
 */
function str2arr($str, $delimiter = ',')
{
    return explode($delimiter, $str);
}

/**
 * 通过$delimiter 对 $arr 进行连接,返回字符串
 * @param $str
 * @param string $delimiter
 * @return string
 */
function arr2str($arr, $delimiter = ',')
{
    return implode($delimiter, $arr);
}


//将数组生成下拉列表
function arr2select($name, $rows, $default, $valueField = 'id', $textField = 'name', $class = 'form-control')
{
    $html = "<select class='{$class}' name='{$name}'>";
    $html .= "<option value=''>--请选择--</option>";
    if ($rows) {
        foreach ($rows as $row) {
            $selected = '';
            if ($row[$valueField] == $default && !isEmpty($default)) {
                $selected = "selected=selected";
            }
            $html .= "<option {$selected} value='{$row[$valueField]}'>{$row[$textField]}</option>";
        }
    }

    $html .= "</select>";
    echo $html;
}


//将数组生成下拉列表
function list2select($name, $rows, $default, $class, $is_all = false)
{
    $html = "<select class='{$class}' name='{$name}'>";
    if ($is_all) {
        $html .= "<option value=''>--请选择--</option>";
    }
    foreach ($rows as $row) {
        $selected = '';
        if ($row == $default) {
            $selected = "selected=selected";
        }
        $html .= "<option {$selected} value='{$row}'>{$row}</option>";
    }
    $html .= "</select>";
    echo $html;
}

function arr2radio($name, $rows, $default, $valueField = 'id', $textField = 'name')
{

    $html = '';
    foreach ($rows as $row) {
        $selected = '';
        if ($row[$valueField] === $default) {
            $selected = "checked='checked'";
        }
        $html .= " <label class='radio-inline'><input type='radio' {$selected}  value='{$row[$valueField]}'  name='{$name}'>{$row[$textField]}</label>";
    }

    echo $html;

}


function url_exists($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    //不下载
    curl_setopt($ch, CURLOPT_NOBODY, 1);
    //设置超时
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
    curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($http_code == 200) {
        return true;
    }
    return false;
}

//二级下拉列表
function list3select($name, $rows, $default, $class)
{
    $html = "<select class='{$class}' name='{$name}'>";
    $html .= "<option value=''>--请选择--</option>";
    foreach ($rows as $row) {
        $selected = '';
        if ($row == $default) {
            $selected = "selected=selected";
        }
        $html .= "<option {$selected} value='{$row}'>{$row}</option>";
    }
    $html .= "</select>";
    echo $html;
}


function get_admin_id()
{
    $user = session('admin');
    if ($user)
        return $user['admin_id'];

    return '';
}

function auth_check($userId, $name = null, $relation = 'or')
{
    if (empty($userId)) {
        return false;
    }

    if ($userId == 1) {
        return true;
    }

    $authObj = new \app\common\tool\Auth();
    if (empty($name)) {
        $request = request();
        $module = $request->module();
        $controller = $request->controller();
        $action = $request->action();
        $name = strtolower($module . "/" . $controller . "/" . $action);
    }
    return $authObj->check($userId, $name, $relation);
}


function get_all_son_id($ids)
{
    $rows = [];
    $ids = rtrim($ids, ',');
    $ids = trim($ids, ',');
    $ids = str2arr($ids, ',');
    foreach ($ids as $id) {
        $cityid = M('region')->where(['pid' => $id])->select();
        if ($cityid) {
            foreach ($cityid as $c) {
                $district_id = M('region')->where(['pid' => $c['id']])->select();
                if ($district_id) {
                    foreach ($district_id as $d) {
                        $rows[] = $d['id'];
                    }
                }
                $rows[] = $c['id'];

            }
        } else {
            $rows[] = $id;
        }

    }
    return arr2str($rows);
}


function cate2select($name, $rows, $default, $class, $valueField = 'id', $textField = 'name')
{
    $html = "<select class='{$class}' name='{$name}'>";
    $html .= "<option value='0'>--根分类--</option>";
    foreach ($rows as $row) {
        $selected = '';
        if ($row[$valueField] == $default) {
            $selected = "selected=selected";
        }
        $html .= "<option {$selected} value='{$row[$valueField]}'>{$row[$textField]}</option>";
    }
    $html .= "</select>";
    echo $html;
}


function showModelError($model)
{
    $errors = $model->getError();
    if (!$errors) {
        return;
    }
    $errorMsg = '';
    if (is_array($errors)) {
        foreach ($errors as $error) {
            $errorMsg .= "{$error}</br>";
        }
    } else {
        $errorMsg .= "{$errors}</br>";
    }
    return $errorMsg;

}



function generateStr($length = 8, $type = "all")
{
    //密码字符集，可任意添加你需要的字符
    $chars = 'abcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()-_[]{}<>~`+=,.;:/?';

    if ($type == "num") {
        $chars = '0123456789';
    } elseif ($type == "char") {
        $chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
    }

    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[mt_rand(0, strlen($chars) - 1)];
    }
    return $password;
}


function setPageCook()
{
    cookie('__forward__', $_SERVER['REQUEST_URI']);
}

/*
 * @param $data
 */
function tlog($data)
{
    ob_start();
    print_r($data);
    echo "\n";
    $result = ob_get_clean();
    $name = env('runtime_path') . date("Y-m-d") . '.log';
    file_put_contents($name, $result, FILE_APPEND);
}


function tlogs($data)
{
    echo '<pre>';
    print_r($data);exit;
}

function send_sms($phone,$str){
    $statusStr = array(
        "0" => "短信发送成功",
        "-1" => "参数不全",
        "-2" => "服务器空间不支持,请确认支持curl或者fsocket，联系您的空间商解决或者更换空间！",
        "30" => "密码错误",
        "40" => "账号不存在",
        "41" => "余额不足",
        "42" => "帐户已过期",
        "43" => "IP地址限制",
        "50" => "内容含有敏感词"
    );
    $smsapi = "http://api.smsbao.com/";
    $user = "joejoe"; //短信平台帐号
    $pass = 'e49f22c403e849cc9e899885aada3c32'; //短信平台密码
    $content=$str;//要发送的短信内容
    //$phone = urlencode($phone);//要发送短信的手机号码
    //$sendurl = $smsapi."wsms?u=".$user."&p=".$pass."&m=".$phone."&c=".urlencode($content);
    $sendurl = $smsapi."sms?u=".$user."&p=".$pass."&m=".$phone."&c=".urlencode($content);
    $result =file_get_contents($sendurl);
    if ($result == 0){
        //Cache::put($key,$code,30000);
        return true;
    }else{
        return $statusStr[$result];
    }
}

function md5_pass($password)
{
    $token='huafeisystem';
    $password='###'.md5(md5($password.$token));
    return $password;
}

/*
 * @param $data
 */
function bqsLog($data)
{
    ob_start();
    print_r($data);
    echo "\n";
    $result = ob_get_clean();
    $name = env('runtime_path').'bqs_' . date("Y-m-d") . '.log';
    file_put_contents($name, $result, FILE_APPEND);
}

function uploadHtml($name, $value, $type)
{
    $timestamp = time();
    $salt = md5('unique_salt' . $timestamp);
    $html = '';
    if ($type == 'single_img') {
        $html = '<div name="' . $name . '" class="4jUploader" value="' . $value . '" type="single" ext="img" timestamp="' . $timestamp . '" salt="' . $salt . '" app_path="" root_path="" upload="' . config('__upload__') . '"></div>';
    } elseif ($type == 'single_file') {
        $html = '<div name="' . $name . '" class="4jUploader" value="' . $value . '" type="single" ext="file" timestamp="' . $timestamp . '" salt="' . $salt . '" app_path="" root_path="" ></div>';
    } elseif ($type == 'multi_img') {
        $html = '<div name="' . $name . '" class="4jUploader" value="' . $value . '" type="multi" ext="img" timestamp="' . $timestamp . '" salt="' . $salt . '" app_path="" root_path="" upload="' . config('__upload__') . '"></div>';
    } elseif ($type == 'multi_file') {
        $html = '<div name="' . $name . '" class="4jUploader" value="' . $value . '" type="multi" ext="file" timestamp="' . $timestamp . '" salt="' . $salt . '" app_path="" root_path="" ></div>';
    }
    return $html;
}


/**
 * 判断是否为空，注意  0不为空，为解决 php内0为空问题
 */
function isEmpty($val)
{
    if ($val === 0 || $val === '0') {
        return false;
    } else {
        return empty($val);
    }
}


/**
 * @param $data
 * @param int $type
 */
function dd($data, $type = 0)
{
    echo '<pre>';
    $type ? var_dump($data) : print_r($data);
    echo '</pre>';
    exit;
}

/**
 * 发送语音短信
 * @param $tel 接收语音电话
 * @param $code 模板变量集
 * @param $TtsCode 模板id
 * @return mixed
 */
function send_soundSMS($tel, $code , $TtsCode ='TTS_129743194')
{
    return \app\common\tool\Sms::sendSoundSMS($tel, $code,$TtsCode);
}

/**
 * @param string $pattern 检索模式 搜索模式 *.txt,*.doc; (同glog方法)
 * @param int $flags
 * @param $pattern
 * @return array
 */
function scan_dir($pattern, $flags = null)
{
    $files = glob($pattern, $flags);
    if (empty($files)) {
        $files = [];
    } else {
        $files = array_map('basename', $files);
    }

    return $files;
}

function showdir($path)
{
    $dh = opendir($path);//打开目录
    while (($d = readdir($dh)) != false) {
        //逐个文件读取，添加!=false条件，是为避免有文件或目录的名称为0
        if ($d == '.' || $d == '..') {//判断是否为.或..，默认都会有
            continue;
        }

        echo $d . "<br />";
        if (is_dir($path . '/' . $d)) {//如果为目录
            showdir($path . '/' . $d);//继续读取该目录下的目录或文件
        } else {
            $s = file_get_contents($path . '/' . $d);
            tlog($s);
        }
    }
}


function imgurl($img)
{
    if (!empty($img)) {
        return config('__upload__') . $img;
    }
    return '';
}

function base_64_two_img($url)
{
    require_once env('root_path') . '/extend/phpqrcode/phpqrcode.php';
    $level = 'M';
    $size = 4;
    ob_start();
    \QRcode::png($url, false, $level, $size);
    $imageString = base64_encode(ob_get_contents());
    ob_end_clean();
    return $imageString;
}

function getOrderNo()
{
    return floor(microtime(true) * 1000) . str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);//生成唯一订单号
}

function contain($str, $search)
{
    $search = trim($search, ',');
    if (strpos($str, $search) !== false) {
        return true;
    } else {
        return false;
    }
}


function getPageSize()
{
    return cookie('page_size') ? cookie('page_size') : 20;
}


/**
 *      把秒数转换为时分秒的格式
 *      @param Int $times 时间，单位 秒
 *      @return String
 */
function secToTime($times){
    $result = '0时0分0秒';
    if ($times>0) {
        $hour = floor($times/3600);
        $minute = floor(($times-3600 * $hour)/60);
        $second = floor((($times-3600 * $hour) - 60 * $minute) % 60);
        $result = $hour.'时'.$minute.'分'.$second.'秒';
    }
    return $result;
}


/**
 * 将手机号中间空格-去掉
 * @param $tel
 * @return mixed
 */
function filter_tel($tel)
{
    $tel = str_replace('　', '', $tel);
    $tel = str_replace(' ', '', $tel);
    $tel = str_replace('-', '', $tel);
    $tel = preg_replace('/[ ]/', '', $tel);
    return $tel;
}

/**
 * 根据生日计算年龄
 * @param $birthday
 * @return bool|false|int
 */
function birthday($birthday){
    $age = strtotime($birthday);
    if($age === false){
        return false;
    }
    list($y1,$m1,$d1) = explode("-",date("Y-m-d",$age));
    $now = strtotime("now");
    list($y2,$m2,$d2) = explode("-",date("Y-m-d",$now));
    $age = $y2 - $y1;
    if((int)($m2.$d2) < (int)($m1.$d1))
        $age -= 1;
    return $age;
}

/**
 * 根据身份证返回籍贯
 * @param $idcard
 * @return mixed
 */
function getCity($idcard)
{
    $code = substr($idcard, 0, 6);
    $city = \think\Db::name('region')->where('id',$code)->find();
    return $city['full_name'];
}

function getAddressCity($id)
{
    $city = \think\Db::name('region')->where('id',$id)->find();
    return $city['name'];
}

function returnTags($tags)
{
    $arr = explode(',',$tags);
    $str = '';
    foreach ($arr as $v){
        $str .= "<small class='label label-info ml15'> $v </small>";
    }
    return $tags;
}

function orderStatus($status=0)
{
    $array = [
        '0' => '待机审',
        '-100'=>'拒单',
        '-300'=>'拒绝放款',
        '100'=>'待提现',
        '101'=>'待放款',
        '102'=>'还款中',
        '104'=>'逾期',
        '200'=>'结清',
    ];
    return $array[$status];
}
function reduceArray($array) {
    $return = [];
    array_walk_recursive($array, function ($x) use (&$return) {
        $return[] = $x;
    });
    return $return;
}


/**
 * 根据手机号 获取所属运营商及归属地
 * @param $mobile
 * @return array|string
 */
function getMobileInfo($mobile){

    if (!preg_match("/^1[3456789]\d{9}$/", $mobile)) {
        return '请输入正确手机号码！';
    }else{
        //调用百度接口
        $phone_json = file_get_contents('http://sp0.baidu.com/8aQDcjqpAAV3otqbppnN2DJv/api.php?query={'.$mobile.'}&resource_id=6004&ie=utf8&oe=utf8&format=json');
        $phone_array = json_decode($phone_json,true);
        $phone_info = array();
        $phone_info['mobile'] = $mobile;
        $phone_info['type'] = $phone_array['data'][0]['type'];
        $phone_info['location'] = $phone_array['data'][0]['prov'].$phone_array['data'][0]['city'];
        return $phone_info;
    }
}


/**
 * 压缩特大数据
 * @param $raw
 * @return bool|string
 */
function gzipRaw($raw)
{
    return base64_encode(gzencode(json_encode($raw)));
}


/**
 * 解压特大数据
 * @param $raw
 * @return bool|string
 */
function ungzipRaw($raw)
{
    return json_decode(gzdecode(base64_decode($raw)), true);
}

/**
 * @param $type
 * @param $id
 * @return string
 */
function getUserInfoType($type, $id)
{
    $model = new \app\common\model\UserInfo();
    return $model::getType($type,$id);
}
