<?php

#-------------------------------------------------------------------------------
# Purpose:     用户接口
# Author:      xiezhongda@gmail.com
# Created:     2017-08-06
# Updated:     2017-08-06
#-------------------------------------------------------------------------------

namespace Home\Controller;

use Home\Controller\ParentController;

class UsersController extends ParentController {
    
    protected $model;
    
    public function __construct() {
        parent::__construct();
        
        $this->model = M('users');
    }
    
    /**
     * 个人中心接口
     */
    public function index() {
        // 身份类型 1发货方 2车主
        $type = I('type', 1);
        if (!in_array($type, [1, 2])) {
            $type = 1;
        }
        
        $user = $this->user_info;
        $user['type'] = $user['type'] ?: $type;
        $user['name'] = $user['name'] ?: $user['nickname'];
        
        
        if ($type == 1) {
            unset($user['car_empty'], $user['car_img'], $user['car_lenght'], $user['car_license_img'], $user['car_load'], $user['car_number_plate'], $user['car_number_plate_type'], $user['car_type']);
        } else {
            unset($user['type_goods']);
        }
        
        $user['id_no'] = hideIdNo($user['id_no']);
        unset($user['register_time'], $user['audit_time'], $user['create_time'], $user['update_time'], $user['id_face_img'], $user['id_word_img'], $user['remark']);
        success($user);
    }
    
    public function fastRegister() {
        $params = $this->user_info;
        if ($params['state'] == 2) {
            error(403, '您已提交过注册申请，请耐心等待后台查看，谢谢！');
        } elseif ($params['state'] == 3) {
            error(403, '您已注册成功，请勿重复注册！');
        }
        
        $params['phone'] = I('phone');
        // 手机验证码
        $phone_code = I('phone_code');
        if (!$phone_code) {
            error(412, '请填写验证码');
        }
        
        // 验证手机验证码
        $true_code = S('send_code_' . $params['phone'] . '_' . $this->open_id);
        if ($phone_code != $true_code) {
            error(412, '验证码错误');
        }
        
        // 判断用户是否存在
        $user = $this->model->where("phone=%d", [$params['phone']])->find();
        if ($user && $user['open_id'] != $this->user_info['open_id']) {
            error(400, '手机号码已注册，请重新填写');
        }
        
        $params['vip'] = 0;
        $params['type'] = 3;
        $params['register_time'] = date('Y-m-d H:i:s');
        $params['update_time'] = date('Y-m-d H:i:s');
        $res = $this->model->save($params);
        if ($res) {
            success('', '注册成功');
        } else {
            error(400, '注册失败，请重试！');
        }
    }
    
    /**
     * 发货方注册接口
     */
    public function registerGoods() {
        $params = $this->user_info;
        if ($params['state'] == 2) {
            error(403, '您已提交过注册申请，请耐心等待后台查看，谢谢！');
        } elseif ($params['state'] == 3) {
            error(403, '您已注册成功，请勿重复注册！');
        }
        
        $params['type_goods'] = I('type_goods');
        $params['phone'] = I('phone');
        $params['name'] = I('name');
        $params['id_no'] = I('id_no');
        $params['header_img'] = I('header_img');
        $params['id_face_img'] = I('id_face_img');
        $params['id_word_img'] = I('id_word_img');
        // 手机验证码
        $phone_code = I('phone_code');
        
        if (!$phone_code) {
            error(412, '请填写验证码');
        }
        
        if (!$params['type_goods'] || !$params['phone'] || !$params['name'] 
                || !in_array($params['type_goods'], [1, 2])) {
            error(412, '参数错误');
        }
        
        // 验证手机验证码
        $true_code = S('send_code_' . $params['phone'] . '_' . $this->open_id);
        if ($phone_code != $true_code) {
            error(412, '验证码错误');
        }
        
        // 判断用户是否存在
        $user = $this->model->where("phone=%d", [$params['phone']])->find();
        if ($user && $user['open_id'] != $this->user_info['open_id']) {
            error(400, '手机号码已注册，请重新填写');
        }
        
        if (!$params['header_img']) {
            unset($params['header_img']);
        }
        
        $params['vip'] = 0;
        $params['type'] = 1;
        $params['state'] = 2;
        $params['register_time'] = date('Y-m-d H:i:s');
        $params['update_time'] = date('Y-m-d H:i:s');
        $res = $this->model->save($params);
        if ($res) {
            // 给运营人员发模板消息
            sendNoticeMessage($params['name'], $params['phone']);
            
            success('', '您的注册信息已提交成功，请等待平台审核，预计6小时内处理，请关注公众号推送通知。');
        } else {
            error(400, '注册失败，请重试！');
        }
    }
    
    /**
     * 车主注册接口
     */
    public function registerCars() {
        $params = $this->user_info;
        if ($params['state'] == 2) {
            error(403, '您已提交过注册申请，请耐心等待后台查看，谢谢！');
        } elseif ($params['state'] == 3) {
            error(403, '您已注册成功，请勿重复注册！');
        }
        
        // 手机
        $params['phone'] = I('phone');
        $phone_code = I('phone_code');
        // 身份信息
        $params['name'] = I('name');
        $params['id_no'] = I('id_no');
        $params['header_img'] = I('header_img');
        $params['id_face_img'] = I('id_face_img');
        $params['id_word_img'] = I('id_word_img');
        $params['car_license_img'] = I('car_license_img');
        // 车辆信息
        $params['car_number_plate_type'] = I('car_number_plate_type', 0);
        $params['car_number_plate'] = I('car_number_plate');
        $params['car_lenght'] = I('car_lenght');
        $params['car_type'] = I('car_type');
        $params['car_load'] = I('car_load');
        $params['car_img'] = I('car_img', '');
        
        // 手机验证码        
        if (!$phone_code) {
            error(412, '请填写验证码');
        }
                
        if (!$params['phone'] || !$params['name'] || !$params['car_number_plate']
                || !$params['car_lenght'] || !$params['car_type']) {
            error(412, '参数错误');
        }
        
        // 验证手机验证码
        $true_code = S('send_code_' . $params['phone'] . '_' . $this->open_id);
        if ($phone_code != $true_code) {
            error(412, '验证码错误');
        }
        
        // 判断用户是否存在
        $user = $this->model->where("phone=%d", [$params['phone']])->find();
        if ($user && $user['open_id'] != $this->user_info['open_id']) {
            error(400, '手机号码已注册，请重新填写');
        }
        
        if (!$params['header_img']) {
            unset($params['header_img']);
        }
        
        $params['vip'] = 0;
        $params['type'] = 2;
        $params['state'] = 2;
        $params['register_time'] = date('Y-m-d H:i:s');
        $params['update_time'] = date('Y-m-d H:i:s');
        $res = $this->model->save($params);
        if ($res) {
            // 给运营人员发模板消息
            sendNoticeMessage($params['name'], $params['phone']);
            
            success('', '您的注册信息已提交成功，请等待平台审核，预计6小时内处理，请关注公众号推送通知。');
        } else {
            error(400, '注册失败，请重试！');
        }
    }
    
    /**
     * 更新车主信息
     */
    public function updateCar() {
        // 车主会员验证
        if ($this->user_info['type'] != 2 || $this->user_info['state'] != 3 || $this->user_info['vip'] != 1) {
            error(401, '您尚未成为车主会员，无法使用该功能，请先成为车主会员');
        }
        
        $car_empty = I('car_empty', -1);
        if (!in_array($car_empty, [0, 1])) {
            error(412, '异常操作');
        }
        
        $params['car_empty'] = $car_empty;
        $res = $this->model->where('id=%d', [$this->user_info['id']])->save($params);
        if ($res) {
            success();
        } else {
            error(400, '操作失败，请重试！');
        }
    }
    
    /**
     * 修改手机号码
     */
    public function updatePhone() {
        $phone = I('post.phone', '');
        $code = I('post.code', '');
        
        if (!$phone || !is_numeric($phone) || strlen($phone) != 11) {
            error(412, '手机号错误');
        }
        if (!$code) {
            error(412, '请填写验证码');
        }
        
        // 允许修改手机号
        $allow_change_key = 'allow_change_phone_' . $this->open_id;
        if (!S($allow_change_key)) {
            error(400, '请先验证原绑定手机号码');
        }
        
        // 判断用户是否存在
        $user = $this->model->where("phone=%d", [$phone])->find();
        if ($user) {
            error(400, '手机号码已注册，请重新填写');
        }
        
        // 验证码
        $true_code = S('send_code_' . $phone . '_' . $this->open_id);
        if ($code != $true_code) {
            error(400, '验证码错误');
        }
        
        $params['phone'] = $phone;
        $res = $this->model->where('id=%d', [$this->user_info['id']])->save($params);
        if ($res) {
            S($allow_change_key, null);
            success();
        } else {
            error(400, '修改失败，请重试！');
        }
    }
    
    /**
     * 验证手机号码
     */
    public function checkPhone() {
        $phone = I('phone');
        if (!$phone || !is_numeric($phone) || strlen($phone) != 11) {
            error(412, '请填写有效手机号');
        }
        
        // 判断用户是否存在
        $user = $this->model->where("phone=%d", [$phone])->find();
        if ($user) {
            error(400, '手机号码已注册，请重新填写');
        } else {
            success();
        }
    }
        
    /**
     * 车主列表页接口
     */
    public function cars() {
        $page = I('page', 1);
        $limit = I('limit', 10);
        
        if (!$page || !$limit) {
            error(412, '参数错误');
        }
        
        $car_empty = I('car_empty', '');
        $car_lenght = I('car_lenght', '');
        $car_type = I('car_type', '');
        $start = I('start', '');
        $end = I('end', '');
        
        $where = ['type' => ['eq', 2], 'state' => ['eq', 3], 'vip' => ['eq', 1]];
        // 发货方类型
        if ($car_empty) {
            $where['car_empty'] = ['eq', 1];
        }
        // 车长
        if ($car_lenght) {
            $where['car_lenght'] = ['eq', $car_lenght];
        }
        // 车型
        if ($car_type) {
            $where['car_type'] = ['eq', $car_type];
        }
        
        $w = [];
        // 出发地
        if ($start) {
            if (substr($start, 2, 4) == '0000') {
                $w['start_province'] = ['eq', $start];
            } elseif (substr($start, 4, 2) == '00') {
                $w['start_city'] = ['eq', $start];
            } else {
                $w['start_district'] = ['eq', $start];
            }
        }
        // 目的地
        if ($end) {
            if (substr($end, 2, 4) == '0000') {
                $w['end_province'] = ['eq', $end];
            } elseif (substr($end, 4, 2) == '00') {
                $w['end_city'] = ['eq', $end];
            } else {
                $w['end_district'] = ['eq', $end];
            }
        }
           
        if ($w) {
            $uids = M('lines')->where($w)->getField('user_id', true);
            $where['id'] = $uids ? ['in', $uids] : 0;
        }
                
        $cars = $this->model->field('id,name,open_id,header_img,car_empty,car_number_plate,car_lenght,car_type')->where($where)->order('update_time desc')->page($page . ',' . $limit)->select();
        $totel = $this->model->where($where)->count();
        
        $list = [];
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

                $car['location'] = str_replace(['省', '自治区'], '', $city['province']) . ' 离我' . $distance . '公里';
            } elseif (!$user_location) {
                $car['location'] = '您未授权定位，请重新关注';
            }
            $list[$distance . $k] = $car;
        }
        
        ksort($list);
        success(['list' => array_values($list), 'datacount' => $totel]);
    }
    
    /**
     * 车主详情页
     */
    public function car() {
        $id = I('id', '');
        if (!$id) {
            error(412, '参数错误');
        }
        
        $user = $this->model->field('id,name,type,open_id,id_no,header_img,phone,car_empty,car_number_plate,car_lenght,car_type,car_load,vip')->where('id=%d and type=2 and state=3', [$id])->find();
        if ($user) {
            // 获取车主常跑线
            $user['lines'] = [];
            $lines = M('lines')->where('user_id=%d', [$user['id']])->order('create_time desc')->select();
            foreach ($lines as $line) {
                $user['lines'][] = [
                    'start' => $line['start_province_name'] . $line['start_city_name'] . $line['start_district_name'],
                    'end' => $line['end_province_name'] . $line['end_city_name'] . $line['end_district_name'],
                ];
            }
            
            // 
            $user['id_no'] = hideIdNo($user['id_no']);
        }
        
        // 发货方会员验证
        if ($this->user_info['type'] != 1 || $this->user_info['state'] != 3) {
            $user['phone'] = substr($user['phone'], 0, 3) . '****' . substr($user['phone'], 7);
        }
        
        success($user);
    }
    
}
