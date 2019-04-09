<?php

#-------------------------------------------------------------------------------
# Purpose:     货源信息
# Author:      xiezhongda@gmail.com
# Created:     2017-08-12
# Updated:     2017-08-12
#-------------------------------------------------------------------------------

namespace Admin\Controller;

use Admin\Controller\ParentController;

class GoodsController extends ParentController {
    
    protected $model;

    public function __construct() {
        parent::__construct();
        
        $this->model = M('goods');
    }
    
    /**
     * 列表页
     */
    public function index() {
        $page_size = C('pageSize');
        $p = I('p/d', 1);
        
        if (I('user_type_goods')) $search['user_type_goods'] = I('user_type_goods');
        if (I('user_nickname')) $search['user_nickname'] = I('user_nickname');
        if (I('user_phone')) $search['user_phone'] = I('user_phone');
        if (I('user_name')) $search['user_name'] = I('user_name');
        
        // where(
        $w = [];
        if ($search['user_type_goods']) {
            $w['user_type_goods'] = ['eq', $search['user_type_goods']];
        }
        if ($search['user_nickname']) {
            $w['user_nickname'] = ['like', '%' . $search['user_nickname'] . '%'];
        }
        if ($search['user_phone']) {
            $w['user_phone'] = ['like', '%' . $search['user_phone'] . '%'];
        }
        if ($search['user_name']) {
            $w['user_name'] = ['like', '%' . $search['user_name'] . '%'];
        }
        $total = $this->model->where($w)->count();
        
        $pageInfo = pageCss($total, $page_size);
        $show = $pageInfo['show'];
        $limit = $pageInfo['limit'];
        
        // 获取列表数据
        $info = $this->model->where($w)->order('update_time desc')->limit($limit)->select();
        $max_p = ceil($total / $page_size);
        $max_p = $max_p < 1 ? 1 : $max_p;
        $p = $p > $max_p ? $max_p : $p;
        
        $this->assign('p', $p);
        $this->assign('page', $show);
        $this->assign('info', $info);
        $this->assign('search', $search);
        $this->display();
    }
    
    public function add() {
        $wap = isMobile();
        
        if (IS_POST) {
            $params = I('post.');
            $params['user_name'] = $params['user_name'] ?: '车来货往客服发布';
            
            // 参数验证
//            foreach ($params as $key => $value) {
//                if (in_array($key, ['user_id_no'])) continue;
//                
//                if (!$value) {
//                    $this->redirect('/admin/goods/add', [], 1, '请填写所有参数');
//                }
//            }
            
            $params['is_admin'] = 1;
            $params['user_id'] = 32;
            $params['user_nickname'] = $params['user_name'];
            $params['user_header_img'] = 'http://wx.qlogo.cn/mmopen/vi_32/QOWAqs12nPE80sxZBpX42TItrpGm4qPhYgf9WVmZqIhInrtEeQo93BicgCOXp3kVheRHNaVzib9IotYa1guw6rhQ/0';
            $params['user_type_goods'] = 1;
            
            $res = $this->model->add($params);            
            if ($res) {
                // 记录日志
                addLog($this->admin['id'], $this->admin['username'], '货源管理', '新增货源', json_encode($params));

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
                
                $url = $wap ? '/admin/goods/add' : '/admin/goods/index';
                $this->redirect($url, [], 1, '操作成功');
            } else {
                $this->redirect('/admin/goods/add', [], 1, '操作失败');
            }
        }
        
        $this->assign('wap', $wap);
        $this->display();
    }
    
    /**
     * 删除货源
     */
    public function delete() {
        $id = I('id');
        if (!$id) {
            $this->redirect('/admin/goods/index', [], 1, '参数错误');
        }
        
        $good = $this->model->where('id=%d', [$id])->find();
        if (!isset($good['id']) || $good['is_admin'] != 1) {
            $this->redirect('/admin/goods/index', [], 1, '非法操作');
        }

        $res = $this->model->where('id=' . $good['id'])->delete();
        if ($res) {
            // 记录日志
            addLog($this->admin['id'], $this->admin['username'], '货源管理', '删除货源', $id);

            $this->redirect('/admin/goods/index', [], 1, '操作成功');
        } else {
            $this->redirect('/admin/goods/index', [], 1, '操作失败');
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
    
}
