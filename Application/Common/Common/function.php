<?php


use EasyWeChat\Foundation\Application;

/**
 * 获取用户信息
 * @param type $openId
 * @return type
 */
function getWechatUserInfo($openId) {
    $app = new Application(C('OPTIONS'));
    $userService = $app->user;
    
    $user = $userService->get($openId);
    return $user;
}

/**
 * 发送审核结果模板消息
 * @param type $openId
 * @return type
 */
function sendNoticeMessage($user, $phone) {
    $app = new Application(C('OPTIONS'));
    $notice = $app->notice;

    $templateId = 'ltMY094Gmi-_i-NKV8IkrIf3YsRAapfQ-nwb1F1AqP4';
    $url = '';
    $data = array(
        "first"   => '有用户提交注册申请，请前往后台审核',
        "keyword1" => $user,
        "keyword2" => $phone,
        "keyword3" => date('Y-m-d H:i'),
        "remark"   => '请务必在 6 小时内完成审核。',
    );
    
    $open_ids = ['o67l21tcH2bKFl2ePQZfsi2OUFm8', 'o67l21om5SQXCvsmwtscpE7TPinU', 'o67l21uaNhDUL_thj4hpT0q8SJ3E'];
    foreach ($open_ids as $open_id) {
        $res = $notice->uses($templateId)->withUrl($url)->andData($data)->andReceiver($open_id)->send();
    }
    return $res;
}

/**
 * 货源消息提醒
 * @param type $openId
 * @return type
 */
function sendGoodMessage($open_id, $start, $end, $good_info, $good_id, $length, $type) {
    // 判断用户是否关注
    $user = getWechatUserInfo($open_id);
    if (!$user || !$user->subscribe) {
        return false;
    }
    
    $app = new Application(C('OPTIONS'));
    $notice = $app->notice;

    $templateId = 'BABqLBTmKuKUCc0sL1eoAJXze-zimsi_sjL9vkuRb4c';
    $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . C('APP_ID') . '&redirect_uri=' . urlencode(APP_HOME . '/Carowner/deliver.html?id=' . $good_id) . '&response_type=code&scope=snsapi_userinfo&state=1#wechat_redirect';
    $data = array(
        "first"   => '您有一条新货源消息',
        "keyword1" => $start,
        "keyword2" => $end,
        "keyword3" => $good_info,
        "keyword4" => $length . '/' . $type,
        "keyword5" => '待定',
        "remark"   => '点击详情查看货源详细信息',
    );
    
    $res = $notice->uses($templateId)->withUrl($url)->andData($data)->andReceiver($open_id)->send();
    return $res;
}

/**
 * 货源发布成功提醒
 * @param type $openId
 * @return type
 */
function addGoodMessage($open_id, $start, $start_name, $end, $end_name, $s, $e) {
    $app = new Application(C('OPTIONS'));
    $notice = $app->notice;

    $templateId = 'jN6pRBbOWZyJ3LI424R_FS5M8nCYQjS0X5yJnzItki4';
    $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . C('APP_ID') . '&redirect_uri=' . urlencode(APP_HOME . '/Deliver/find.html?start=' . $start . '&start_name=' . $start_name . '&end=' . $end . '&end_name=' . $end_name) . '&response_type=code&scope=snsapi_userinfo&state=1#wechat_redirect';
    $data = array(
        "first"   => '您的货源已发布成功',
        "keyword1" => date('Y-m-d H:i'),
        "keyword2" => '您发布了从 ' . $s . ' 至 ' . $e . '的货物，',
        "remark"   => '点击详情查看符合路线的车主',
    );
    
    $res = $notice->uses($templateId)->withUrl($url)->andData($data)->andReceiver($open_id)->send();
    return $res;
}

/**
 * 跳转
 * @param type $url
 */
function redirectTo($url) {
    header('Location:' . $url);
    exit;
}

/**
 * 成功
 * @param type $data
 * @param type $message
 * @param string $redirect
 */
function success($data = '', $message = '操作成功') {
    header('Content-Type:application/json; charset=utf-8');
    if (isset($data['datacount']) && $data['datacount'] == 0) {
        $code = 2;
    } else {
        $code = 1;
    }
    echo json_encode(array('code' => $code, 'data' => $data, 'msg' => $message));
    exit;
}

/**
 * 失败
 * @param type $code
 * @param type $message
 */
function error($code = 400, $message = '未知错误') {
    header('Content-Type:application/json; charset=utf-8');
    echo json_encode(array('code' => 0, 'data' => '', 'msg' => $message));
    exit;
}

/**
 * http 请求
 * @param type $url
 * @param type $data
 * @param type $header
 * @return type
 */
function httpsRequest($url, $data = null, $header = ['Expect:  ']) {
    $curl = curl_init();

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

    if (!empty($data)) {
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    }

    $output = curl_exec($curl);
    curl_close($curl);

    return $output;
}

/**
 * 根据地址获取经纬度
 * @param type $province
 * @param type $city
 * @param type $district
 * @return boolean
 */
function getLatAndLng($province, $city = '', $district = '') {
    if (!$province) {
        return false;
    }
    
    $address = $province . $city . $district;
    $api = 'http://api.map.baidu.com/geocoder?address=' . $address . '&output=json&key=AEdbcead812d674176a233342e989501&city=' . $province;
    
    $info = httpsRequest($api);
    $location = json_decode($info, true);
    if ($location['status'] == 'OK' && isset($location['result']['location'])) {
        return $location['result']['location'];
    }
    
    return false;
}

/**
 * 获取定位地址
 * @param type $openid
 * @return string
 */
function getLocation($openid) {
    $location = json_decode(S('location_' . $openid), true);
    if (isset($location['Latitude']) && isset($location['Longitude'])) {
        $city = getCityByLocation($location['Latitude'], $location['Longitude']);

        $addr = [
            'province'      => substr($city['adcode'], 0, 2) . '0000',
            'city'          => substr($city['adcode'], 0, 4) . '00',
            'district'      => $city['adcode'],
            'province_name' => $city['province'],
            'city_name'     => $city['city'],
            'district_name' => $city['district']
        ];
    } else {
        $addr = [
            'province'      => '150000',
            'city'          => '150700',
            'district'      => '150781',
            'province_name' => '内蒙古',
            'city_name'     => '呼伦贝尔市',
            'district_name' => '满洲里市'
        ];
    }
    
    return $addr;
}

/**
 * 根据经纬度获取定位信息
 * @param type $lat
 * @param type $lng
 * @return string
 */
function getCityByLocation($lat, $lng) {
    if (!$lat || !$lng) {
        return '';
    }
    
    $api = 'http://api.map.baidu.com/geocoder/v2/?callback=renderReverse&location=' . $lat . ',' . $lng . '&output=json&pois=1&ak=AEdbcead812d674176a233342e989501';

    $jsonp = httpsRequest($api);
    $location = json_decode(rtrim(str_replace('renderReverse&&renderReverse(', '', $jsonp), ')'), true);
    
    if (isset($location['result']['addressComponent'])) {
        return $location['result']['addressComponent'];
    }
    
    return '';
}

/**   
 * 根据两点间的经纬度计算距离（公里）
 * @param float $lat 纬度值  
 * @param float $lng 经度值  
*/
function getDistance($lat1, $lng1, $lat2, $lng2) {
    $earthRadius = 6378.138; //approximate radius of earth in meters   
    $lat1 = ($lat1 * pi() ) / 180;
    $lng1 = ($lng1 * pi() ) / 180;
    $lat2 = ($lat2 * pi() ) / 180;
    $lng2 = ($lng2 * pi() ) / 180;
    $calcLongitude = $lng2 - $lng1;
    $calcLatitude = $lat2 - $lat1;
    $stepOne = pow(sin($calcLatitude / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($calcLongitude / 2), 2);
    $stepTwo = 2 * asin(sqrt($stepOne));
    $calculatedDistance = $earthRadius * $stepTwo;
    
    return round($calculatedDistance, 2);
}

/**
 * 身份证脱敏
 * @param type $value
 * @return type
 */
function hideIdNo($value) {
    if (strlen($value) < 12) return $value;
    
    return substr($value, 0, 4) . '****' . substr($value, -4);
}

/**
 * 
 * @staticvar type $ip
 * @param type $type
 * @return type
 */
function getClientIp($type = 0) {
    $type = $type ? 1 : 0;
    static $ip = null;
    if ($ip !== null) {
        return $ip[$type];
    }

    if (isset($_SERVER['HTTP_X_REAL_IP'])) { //nginx 代理模式下，获取客户端真实IP
        $ip = $_SERVER['HTTP_X_REAL_IP'];
    } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) { //客户端的ip
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {//浏览当前页面的用户计算机的网关
        $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $pos = array_search('unknown', $arr);
        if (false !== $pos) unset($arr[$pos]);
        $ip = trim($arr[0]);
    } elseif (isset($_SERVER['REMOTE_ADDR'])) {//浏览当前页面的用户计算机的ip地址
        $ip = $_SERVER['REMOTE_ADDR'];
    } else {
        $ip = '127.0.0.1';
    }

    // IP地址合法验证
    $long = sprintf("%u", ip2long($ip));
    $ip = $long ? array($ip, $long) : array($ip, 0);
    return $ip[$type];
}

/* 移动端判断 */

function isMobile() {
    // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
    if (isset($_SERVER['HTTP_X_WAP_PROFILE'])) {
        return true;
    }
    // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
    if (isset($_SERVER['HTTP_VIA'])) {
        // 找不到为flase,否则为true
        return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
    }
    // 脑残法，判断手机发送的客户端标志,兼容性有待提高
    if (isset($_SERVER['HTTP_USER_AGENT'])) {
        $clientkeywords = array('nokia',
            'sony',
            'ericsson',
            'mot',
            'samsung',
            'htc',
            'sgh',
            'lg',
            'sharp',
            'sie-',
            'philips',
            'panasonic',
            'alcatel',
            'lenovo',
            'iphone',
            'ipod',
            'blackberry',
            'meizu',
            'android',
            'netfront',
            'symbian',
            'ucweb',
            'windowsce',
            'palm',
            'operamini',
            'operamobi',
            'openwave',
            'nexusone',
            'cldc',
            'midp',
            'wap',
            'mobile'
        );
        // 从HTTP_USER_AGENT中查找手机浏览器的关键字
        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
            return true;
        }
    }
    // 协议法，因为有可能不准确，放到最后判断
    if (isset($_SERVER['HTTP_ACCEPT'])) {
        // 如果只支持wml并且不支持html那一定是移动设备
        // 如果支持wml和html但是wml在html之前则是移动设备
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
            return true;
        }
    }
    return false;
}
