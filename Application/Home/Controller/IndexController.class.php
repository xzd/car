<?php

namespace Home\Controller;

use Aliyun\Core\Config;
use Home\Controller\ParentController;

class IndexController extends ParentController {

    public function shop() {
        $cate = I('type/d', 1);
        if (!$cate || !in_array($cate, [1, 2, 3, 4, 5, 6])) {
            error(400, '信息不存在');
        }
        
        // 合作企业
        $shops = M('ads')->where('type=4 and category=' . $cate)->order('update_time desc')->select();
        
        success($shops);
    }
    
    public function index() {
        // 关注
        $follow = M('users')->count();
        
        // 广告
        $ad1 = M('ads')->where('type=1')->limit(3)->select();
        $ad2 = M('ads')->where('type=3')->limit(3)->select();
        
        // 货源
        $where = ['state' => ['eq', 1]];
        // 非车主用户只能查看公开货源信息
        if ($this->user_info['type'] != 2) {
            $where['public'] = ['eq', 1];
        }
        $goods = M('goods')->field('id,user_name,user_header_img,user_type_goods,goods_type,goods_num,goods_unit,car_lenght,car_type,update_time,end_province_name,end_city_name,end_district_name,start_province_name,start_city_name,start_district_name,money')->where($where)->order('update_time desc')->limit(2)->select();
        foreach ($goods as &$good) {
            $good['image'] = 'http://www.chelaihuowang.xin/Public/home/images/chezhu/goods1_09.png';
        }
        
        // 车源
        $where = ['type' => ['eq', 2], 'state' => ['eq', 3], 'vip' => ['eq', 1]];
        $cars = M('users')->field('id,name,open_id,header_img,car_img,car_empty,car_number_plate,car_lenght,car_type')->where($where)->order('update_time desc')->limit(2)->select();
        
        $user_location = json_decode(S('location_' . $this->user_info['open_id']), true);
        foreach ($cars as $k => $car) {
            $car_location = json_decode(S('location_' . $car['open_id']), true);

            $distance = 9999;
            $car['location'] = '车主离线，获取失败';
            if ($car_location && $user_location) {
                // 获取定位
                $city = getCityByLocation($car_location['Latitude'], $car_location['Longitude']);

                // 计算距离
                $distance = getDistance($user_location['Latitude'], $user_location['Longitude'], $car_location['Latitude'], $car_location['Longitude']);

                $car['location'] = $city['province'] . ' 离我' . $distance . '公里';
            } elseif (!$user_location) {
                $car['location'] = '您未授权定位，请重新关注';
            }
            
            if (!$car['car_img']) $car['car_img'] = $car['header_img'];
            $list[$distance . $k] = $car;
        }
        
        ksort($list);
        
        // 合作企业
        $enterprises = M('ads')->where('type=2')->order('update_time desc')->select();
        
        $data = [
            'follow' => $follow,
            'clinic' => S('click_num'),
            'ad1'    => array_column($ad1, 'image'),
            'ad2'    => array_column($ad2, 'image'),
            'goods'  => $goods,
            'cars'   => array_values($list),
            'enterprises' => $enterprises,
            'userinfo' => $this->user_info
        ];
        
        success($data);
    }
    
    public function info() {
        $id = I('id/d');
        if (!$id) {
            error(400, '参数错误');
        }
        
        // 合作企业
        $enterprises = M('ads')->where('type=2 and id = %d', [$id])->find();
        
        if ($enterprises) {
            success($enterprises);
        } else {
            error(400, '信息不存在');
        }
    }

    /**
     * 获取省市区信息
     */
    public function citys() {
        // 编码, 为 0 时获取
        $code = I('code', 0);
        $model = M('locations');

        $data = $model->field('code,name')->where('parent=%d', [$code])->select();
        success($data);
    }

    /**
     * 获取局站信息
     */
    public function sites() {
        // 编码, 为 0 时获取
        $code = I('code', 0);
        $model = M('freight_locations');

        $data = $model->field('code,name')->where('parent=%d', [$code])->select();
        success($data);
    }

    /**
     * 上传图片接口
     */
    public function upload() {
        if ($_FILES) {
            $config = array(
                'mimes' => array('image/jpeg', 'image/png', 'image/gif'), //允许上传的文件MiMe类型
                'maxSize' => 10483760, // 上传的文件大小限制 (0-不做限制)
                'exts' => array('jpg', 'jpeg', 'png'), // 允许上传的文件后缀
                'rootPath' => './upload/', // 保存根路径
                'savePath' => '', // 保存路径
            );

            $upload = new \Think\Upload($config);

            $filedata = $_FILES['file'];
            $info = $upload->uploadOne($filedata);
            if (!$info) {
                echo json_encode(array('code' => 400, 'data' => '', 'msg' => $upload->getError()));
                die;
            } else {
                $image = new \Think\Image(); 
                $image->open('./upload/' . $info['savepath'] . $info['savename']);
                // 按照原图的比例生成一个最大为150*150的缩略图并保存为thumb.jpg
                
                $savename = explode('.', $info['savename']);
                $image->thumb(400, 400)->save('./upload/' . $info['savepath'] . $savename[0] . '_thumb.' . $savename[1]);
                
                echo json_encode(array('code' => 1, 'data' => APP_HOME . '/upload/' . $info['savepath'] . $info['savename'], 'msg' => ''));
                die;
                //success(APP_HOME . '/upload/' . $info['savepath'] . $info['savename']);
            }
        }
    }

    /**
     * base64 图片上传
     */
    public function upload2() {
        $base64_img = I('post.img');
        $header_img = I('post.header_img/d', 0);
        
        // post的数据里面，加号会被替换为空格，需要重新替换回来，如果不是post的数据，则注释掉这一行
        if(preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_img, $result)){
            // 后缀
            $type = $result[2];
            if (in_array($type, array('jpg', 'jpeg', 'png'))) {
                // 
                $dir = '/upload/' . date('Y-m-d');
		!is_dir('.' . $dir) && mkdir('.' . $dir, 0777);
                
                $file_name = '/' . uniqid();
                $new_file = $dir . $file_name . '.' . $type;
                $return_file = $dir . $file_name . '_thumb.' . $type;
                                
                if (file_put_contents('.' . $new_file, base64_decode(str_replace($result[1], '', $base64_img)))) {
                    // 更新用户头像
                    if ($header_img) {
                        M('users')->where('id=%d', [$this->user_info['id']])->save(['header_img' => APP_HOME . $new_file]);
                        M('goods')->where('user_id=%d', [$this->user_info['id']])->save(['user_header_img' => APP_HOME . $new_file]);
                    }
                
                    $image = new \Think\Image(); 
                    $image->open('.' . $new_file);
                    // 按照原图的比例生成一个最大为150*150的缩略图并保存为thumb.jpg
                    $image->thumb(400, 400)->save('.' . $return_file);
                    
                    success(APP_HOME . $new_file);
                } else {
                    error(400, '上传失败');
                }
            } else {
                error(400, '格式错误');
            }
        } else {
            error(400, '文件错误');
        }
    }

    /**
     * 发送短信验证码
     */
    public function sendCode() {
        $value = I('post.phone', '');
        if (!$value || !is_numeric($value) || strlen($value) != 11) {
            error(412, '手机号错误');
        }

        $send_code = 'send_code_' . $value;
        $send_code_key = 'send_code_' . $value . '_' . $this->open_id;
        $send_code_num_key = 'send_code_num_' . $value . '_' . $this->open_id;

        // 读取今日发送短信次数
        $send_num = S($send_code_num_key) ?: 0;
        if ($send_num > 5) {
            error(412, '发送太频繁，请过24小时后重新发送');
        }
        
        // 短信间隔1分钟
        if (S($send_code)) {
            error(412, '发送太频繁，请过1分钟后重新发送');
        }

        // 随机数
        $code = rand(100000, 999999);

        // 加载区域结点配置
        Config::load();

        // 实例化
        $Sms = new \Sms('LTAIhccu5GLgdltG', 'xKCSGK8TDBoF3N9xcQ5krFuozRAnKS');

        // 发送短信
        $response = $Sms->sendSms(
                '车来货往', // 短信签名
                'SMS_94485002', // 短信模板编号
                $value, // 短信接收者
                ['number' => $code]
        );

        if (!isset($response->Code) || $response->Code != 'OK') {
            error(400, '短信发送失败，请重试');
        }

        S($send_code, 1, 60);
        S($send_code_key, $code, 600);
        S($send_code_num_key, ($send_num + 1), 86400);

        // 短信发送日志
        M('sms_logs')->add(['phone' => $value, 'code' => $code, 'open_id' => $this->open_id]);

        success();
    }

    /**
     * 验证原绑定手机号
     */
    public function checkCode() {
        $phone = I('post.phone', '');
        $code = I('post.code', '');

        if ($this->user_info['state'] != 3) {
            error(401, '您尚未注册，无法使用该功能');
        }

        if (!$phone || !is_numeric($phone) || strlen($phone) != 11 || $phone != $this->user_info['phone']) {
            error(412, '手机号错误');
        }
        if (!$code) {
            error(412, '请填写验证码');
        }

        $true_code = S('send_code_' . $phone . '_' . $this->open_id);
        if ($code != $true_code) {
            error(412, '验证码错误');
        }

        // 允许修改手机号
        S('allow_change_phone_' . $this->open_id, 600);

        success();
    }

    /**
     * 计算出发地和目的地距离
     */
    public function distance() {
        $start_province = I('start_province');
        $start_city = I('start_city', '');
        $start_district = I('start_district', '');
        $end_province = I('end_province');
        $end_city = I('end_city', '');
        $end_district = I('end_district', '');
        
        if (!$start_province || !$end_province) {
            error(401, '参数错误');
        }
        
        // 获取出发地
        $start = getLatAndLng($start_province, $start_city, $start_district);
        $end = getLatAndLng($end_province, $end_city, $end_district);
        
        // 
        if (!isset($start['lat']) || !isset($start['lng']) || !isset($end['lat']) || !isset($end['lng'])) {
            error(401, '获取失败');
        }
        
        // 计算距离
        $distance = getDistance($start['lat'], $start['lng'], $end['lat'], $end['lng']);
        success($distance . ' 公里');
    }
    
    
    public function freight() {
        $start = I('start');
        $end = I('end');
        $type = I('type');
        
        if (!$start || !$end || !$type || !in_array($type, [1,2,3,4])) {
            error(400, '参数错误');
        }
        
        $info = M('freights')->where('type=%d and start_district=%d and end_city=%d', [$type, $start, $end])->find();
        if (!$info) {
            error(400, '未查询到运费信息');
        }
        
        $data = [];
        if ($type == 2) {
            $price_info = explode('-', $info['price']);
            
            $data['mdm'] = $price_info[0];
            $data['mdz'] = $price_info[1];
            $data['zdm'] = $price_info[2];
            $data['zdz'] = $price_info[3];
        } else {
            $data['price'] = $info['price'];
        }
        
        success($data);
    }
    
}
