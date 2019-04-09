<?php

#-------------------------------------------------------------------------------
# Purpose:     货源接口
# Author:      xiezhongda@gmail.com
# Created:     2017-08-05
# Updated:     2017-08-05
#-------------------------------------------------------------------------------

namespace Home\Controller;

use Home\Controller\ParentController;

class GoodsController extends ParentController {
    
    protected $model;

    public function __construct() {
        parent::__construct();
        
        $this->model = M('goods');
    }
    
    /**
     * 我的货源页接口
     */
    public function index() {
        $page = I('page', 1);
        $limit = I('limit', 10);
        $state = I('state', 1);
        
        if (!$page || !$limit || !in_array($state, [1, 2])) {
            error(412, '参数错误');
        }
                
        $order = $state == 1 ? 'create_time desc' : 'close_time desc';
        $goods = $this->model->field('id,car_lenght,car_type,update_time as create_time,close_time,end_province,end_province_name,end_city,end_city_name,end_district,end_district_name,start_province,start_province_name,start_city,start_city_name,start_district,start_district_name,goods_type,goods_num,goods_unit,state,money')->where('user_id=%d and state=%d', [$this->user_info['id'], $state])->order($order)->page($page . ',' . $limit)->select();
        $totel = $this->model->where('user_id=%d and state=%d', [$this->user_info['id'], $state])->count();
        
        foreach ($goods as &$good) {
            // 获取来电的司机
            $good['calls'] = M('calls')
                    ->field('car_users.header_img,car_users.name,car_users.car_number_plate,car_users.phone,car_calls.create_time')
                    ->join('car_users ON car_calls.user_id = car_users.id')
                    ->where('car_calls.good_id=%d', [$good['id']])
                    ->select();
        }
        
        success(['list' => $goods, 'datacount' => $totel]);
    }
    
    /**
     * 关闭货源重发
     */
    public function reset() {
        $id = I('post.id');
        if (!$id) {
            error(412, '参数错误');
        }

        $good = $this->model->where('id=%d', [$id])->find();
        if (!isset($good['id']) || $good['user_id'] != $this->user_info['id']) {
            error(401, '非法操作');
        }
        
        $update['state'] = 1;
        $update['update_time'] = date('Y-m-d H:i:s');
        $res = $this->model->where('id=' . $good['id'])->save($update);
        if ($res) {
            success();
        } else {
            error(400, '重发失败，请重试！');
        }
    }
    
    /**
     * 关闭货源
     */
    public function close() {
        $id = I('post.id');
        if (!$id) {
            error(412, '参数错误');
        }

        $good = $this->model->where('id=%d', [$id])->find();
        if (!isset($good['id']) || $good['user_id'] != $this->user_info['id'] || $good['state'] != 1) {
            error(401, '非法操作');
        }
        
        $update['state'] = 2;
        $update['close_time'] = date('Y-m-d H:i:s');
        $res = $this->model->where('id=' . $good['id'])->save($update);
        if ($res) {
            success();
        } else {
            error(400, '关闭失败，请重试！');
        }
    }
    
    /**
     * 删除已关闭货源
     */
    public function delete() {
        $ids = I('post.ids');
        if (!$ids) {
            error(412, '参数错误');
        }
        
        $id_arr = explode(',', $ids);
        foreach ($id_arr as $id) {
            $good = $this->model->where('id=%d', [$id])->find();
            if (!isset($good['id']) || $good['user_id'] != $this->user_info['id'] || $good['state'] != 2) {
                error(401, '非法操作');
            }

            $update['state'] = 3;
            $res = $this->model->where('id=' . $good['id'])->save($update);
            if (!$res) {
                error(400, '关闭失败，请重试！');
            }
        }
        
        success();
    }
    
    /**
     * 发布货源接口
     */
    public function add() {
        // 发货方会员验证
        if ($this->user_info['type'] != 1 || $this->user_info['state'] != 3) {
            error(401, '您尚未成为发货方会员，无法使用该功能，请先成为会员');
        }
        
        $params['start_province'] = I('post.start_province');
        $params['start_province_name'] = I('post.start_province_name');
        $params['end_province'] = I('post.end_province');
        $params['end_province_name'] = I('post.end_province_name');
        $params['car_lenght'] = I('post.car_lenght');
        $params['car_type'] = I('post.car_type');
        $params['goods_type'] = I('post.goods_type');
        $params['goods_num'] = I('post.goods_num');
        $params['goods_unit'] = I('post.goods_unit');
        $params['user_id'] = $this->user_info['id'];
        $params['user_name'] = $this->user_info['name'];
        $params['user_phone'] = $this->user_info['phone'];
        $params['user_nickname'] = $this->user_info['nickname'];
        $params['user_type_goods'] = $this->user_info['type_goods'];
                
        foreach ($params as $value) {
            if (!$value) {
                error(412, '请填写所有信息');
            }
        }
        
        $params['start_city'] = I('post.start_city');
        $params['start_city_name'] = I('post.start_city_name');
        $params['start_district'] = I('post.start_district');
        $params['start_district_name'] = I('post.start_district_name');
        $params['end_city'] = I('post.end_city');
        $params['end_city_name'] = I('post.end_city_name');
        $params['end_district'] = I('post.end_district');
        $params['end_district_name'] = I('post.end_district_name');
        $params['money'] = I('post.money');
        $params['public'] = I('post.public', 1);
        $params['repeat'] = intval(I('post.repeat')) == 1 ? 15 : 0;
        
        $params['user_id_no'] = $this->user_info['id_no'];
        $params['user_header_img'] = $this->user_info['header_img'];
        
        $res = $this->model->add($params);
        if ($res) {
            // 货源发布成功, 给常跑路线符合的车主发模板消息
            $start = $params['start_province_name'];
            $end = $params['end_province_name'];
            $w['start_province'] = ['eq', $params['start_province']];
            $w['end_province'] = ['eq', $params['end_province']];
            if ($params['start_city']) {
                $start .= '-' . $params['start_city_name'];
                $w['start_city'] = ['eq', $params['start_city']];
            }
            if ($params['start_district']) {
                $start .= '-' . $params['start_district_name'];
                $w['start_district'] = ['eq', $params['start_district']];
            }
            if ($params['end_city']) {
                $end .= '-' . $params['end_city_name'];
                $w['end_city'] = ['eq', $params['end_city']];
            }
            if ($params['end_district']) {
                $end .= '-' . $params['end_district_name'];
                $w['end_district'] = ['eq', $params['end_district']];
            }
            
            $open_ids = M('lines')->where($w)->getField('user_open_id', true);
            foreach ($open_ids as $open_id) {
                sendGoodMessage($open_id, $start, $end, $params['goods_type'] . '/' . $params['goods_num'] . $params['goods_unit'], $res, $params['car_lenght'], $params['car_type']);
            }
            
            // 发送模板消息
            $car_start = $params['start_district'] ?: ($params['start_city'] ?: $params['start_province']);
            $car_start_name = $params['start_district_name'] ?: ($params['start_city_name'] ?: $params['start_province_name']);
            $car_end = $params['end_district'] ?: ($params['end_city'] ?: $params['end_province']);
            $car_end_name = $params['end_district_name'] ?: ($params['end_city_name'] ?: $params['end_province_name']);
            
            addGoodMessage($this->user_info['open_id'], $car_start, $car_start_name, $car_end, $car_end_name, $start, $end);
            
            success('', '发布成功');
        } else {
            error(400, '发布失败，请重试！');
        }
    }
    
    /**
     * 货源列表页
     */
    public function lists() {
        $page = I('page', 1);
        $limit = I('limit', 10);
        
        if (!$page || !$limit) {
            error(412, '参数错误');
        }
        
        $type_goods = I('type_goods', '');
        $car_lenght = I('car_lenght', '');
        $car_type = I('car_type', '');
        $starts = I('start', '');
        $ends = I('end', '');
        
        $where = ['state' => ['eq', 1]];
        // 非车主用户只能查看公开货源信息
        if ($this->user_info['type'] != 2) {
            $where['public'] = ['eq', 1];
        }
        // 发货方类型
        if ($type_goods && in_array($type_goods, [1, 2])) {
            $where['user_type_goods'] = ['eq', $type_goods];
        }
        // 车长
        if ($car_lenght) {
            $where['car_lenght'] = ['like', "%$car_lenght%"];
        }
        // 车型
        if ($car_type) {
            $where['car_type'] = ['like', "%$car_type%"];
        }
        // 处发地
        if ($starts) {
            $start_arr = explode(',', $starts);
            
            $start_query = [];
            foreach ($start_arr as $start) {
                if (substr($start, 2, 4) == '0000') {
                    $start_query[] = 'start_province=' . $start;
                } elseif (substr($start, 4, 2) == '00') {
                    $start_query[] = 'start_city=' . $start;
                } else {
                    $start_query[] = 'start_district=' . $start;
                }
            }
            
            if ($start_query) {
                $where['_string'] = '(' . implode(') OR (', $start_query) . ')';
            }
        }
        // 目的地
        if ($ends) {
            $end_arr = explode(',', $ends);
            
            $end_query = [];
            foreach ($end_arr as $end) {
                if (substr($end, 2, 4) == '0000') {
                    $end_query[] = 'end_province=' . $end;
                } elseif (substr($end, 4, 2) == '00') {
                    $end_query[] = 'end_city=' . $end;
                } else {
                    $end_query[] = 'end_district=' . $end;
                }
            }
            
            if ($end_query) {
                if (!isset($where['_string']) || !$where['_string']) {
                    $where['_string'] = '(' . implode(') OR (', $end_query) . ')';
                } else {
                    $where['_string'] .= ') AND ((' . implode(') OR (', $end_query) . ')';
                }
            }
        }
        
        $goods = $this->model->field('id,user_name,user_header_img,user_type_goods,goods_type,goods_num,goods_unit,car_lenght,car_type,update_time,end_province_name,end_city_name,end_district_name,start_province_name,start_city_name,start_district_name,money')->where($where)->order('update_time desc')->page($page . ',' . $limit)->select();
        $totel = $this->model->where($where)->count();
        success(['list' => $goods, 'datacount' => $totel]);
    }
    
    /**
     * 货源详情页接口
     */
    public function detail() {
        $id = I('id', '');
        if (!$id) {
            error(412, '参数错误');
        }
        
        $good = $this->model->field('id,user_name,user_header_img,user_type_goods,user_id_no,user_phone,goods_type,goods_num,goods_unit,car_lenght,car_type,create_time,end_province_name,end_city_name,end_district_name,start_province_name,start_city_name,start_district_name,money')->where('id=%d and state=1', [$id])->find();
        $good['user_id_no'] = hideIdNo($good['user_id_no']);
        
        // 车主会员验证
//        if ($this->user_info['type'] != 2 || $this->user_info['state'] != 3) {
//            $good['user_phone'] = substr($good['user_phone'], 0, 3) . '****' . substr($good['user_phone'], 7);
//        }
        success($good);
    }
    
}
