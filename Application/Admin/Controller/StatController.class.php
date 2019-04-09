<?php

#-------------------------------------------------------------------------------
# Purpose:     数据统计
# Author:      xiezhongda@gmail.com
# Created:     2017-08-12
# Updated:     2017-08-12
#-------------------------------------------------------------------------------

namespace Admin\Controller;

use Admin\Controller\ParentController;

class StatController extends ParentController {
    
    protected $model;
    protected $order;

    public function __construct() {
        parent::__construct();
        
        $this->model = M('users');
        $this->order = M('user_order');
    }
    
    /**
     * 会员数据查询
     */
    public function index() {
        $car_count = $this->model->where('type = 2 and vip = 1')->count();
        $good1_count = $this->model->where('type = 1 and type_goods = 1 and vip = 1')->count();
        $good2_count = $this->model->where('type = 1 and type_goods = 2 and vip = 1')->count();
        
        $page_size = C('pageSize');
        $p = I('p/d', 1);
        
        $search = [];
        if (I('type')) $search['type'] = I('type');
        $search['start_date'] = I('start_date', date('Y-m-01'));
        $search['end_date'] = I('end_date', date('Y-m-d'));
        
        // where(
        $w = ['vip' => 1];
        if (isset($search['type']) && $search['type'] == 1) {
            $w['type'] = 2;
        } elseif (isset($search['type']) && $search['type'] == 2) {
            $w['type'] = 1;
            $w['type_goods'] = 1;
        } elseif (isset($search['type']) && $search['type'] == 3) {
            $w['type'] = 1;
            $w['type_goods'] = 2;
        }
        
        if (isset($search['start_date']) && isset($search['end_date'])) {
            $w['vip_starttime'] = ['BETWEEN', [$search['start_date'], $search['end_date']]];
        }
                
        // 查询条件
        $total = $this->model->where($w)->count();
        
        $pageInfo = pageCss($total, $page_size);
        $show = $pageInfo['show'];
        $limit = $pageInfo['limit'];
        
        // 获取列表数据
        $info = $this->model->where($w)->order('vip_starttime desc')->limit($limit)->select();
        
        $max_p = ceil($total / $page_size);
        $max_p = $max_p < 1 ? 1 : $max_p;
        $p = $p > $max_p ? $max_p : $p;
        
        $this->assign('p', $p);
        $this->assign('page', $show);
        $this->assign('info', $info);
        $this->assign('search', $search);
        
        $this->assign('car_count', $car_count);
        $this->assign('good1_count', $good1_count);
        $this->assign('good2_count', $good2_count);
        $this->display();
    }
    
    /**
     * 注册用户数查询
     */
    public function register() {
        $car_count = $this->model->where('type = 2 and state = 3')->count();
        $good1_count = $this->model->where('type = 1 and type_goods = 1 and state = 3')->count();
        $good2_count = $this->model->where('type = 1 and type_goods = 2 and state = 3')->count();
        
        $page_size = C('pageSize');
        $p = I('p/d', 1);
        
        $search = [];
        if (I('type')) $search['type'] = I('type');
        $search['start_date'] = I('start_date', date('Y-m-01'));
        $search['end_date'] = I('end_date', date('Y-m-d'));
        
        // where(
        $w = ['state' => 3];
        if (isset($search['type']) && $search['type'] == 1) {
            $w['type'] = 2;
        } elseif (isset($search['type']) && $search['type'] == 2) {
            $w['type'] = 1;
            $w['type_goods'] = 1;
        } elseif (isset($search['type']) && $search['type'] == 3) {
            $w['type'] = 1;
            $w['type_goods'] = 2;
        }
        
        if (isset($search['start_date']) && isset($search['end_date'])) {
            $w['register_time'] = ['BETWEEN', [$search['start_date'], date('Y-m-d', strtotime($search['end_date'] . ' +1 day'))]];
        }
                
        // 查询条件
        $total = $this->model->where($w)->count();
        
        $pageInfo = pageCss($total, $page_size);
        $show = $pageInfo['show'];
        $limit = $pageInfo['limit'];
        
        // 获取列表数据
        $info = $this->model->where($w)->order('register_time desc')->limit($limit)->select();
        
        $max_p = ceil($total / $page_size);
        $max_p = $max_p < 1 ? 1 : $max_p;
        $p = $p > $max_p ? $max_p : $p;
        
        $this->assign('p', $p);
        $this->assign('page', $show);
        $this->assign('info', $info);
        $this->assign('search', $search);
        
        $this->assign('car_count', $car_count);
        $this->assign('good1_count', $good1_count);
        $this->assign('good2_count', $good2_count);
        $this->display();
    }
    
    /**
     * 会员收益查询
     */
    public function earnings() {
        $page_size = C('pageSize');
        $p = I('p/d', 1);
        
        $search = [];
        if (I('type')) $search['type'] = I('type');
        $search['start_date'] = I('start_date', date('Y-m-01'));
        $search['end_date'] = I('end_date', date('Y-m-d'));
        
        // where(
        $w = ['state' => 1];
        if (isset($search['type'])) {
            $w['product_id'] = $search['type'];
        }
        if (isset($search['start_date']) && isset($search['end_date'])) {
            $w['create_time'] = ['BETWEEN', [$search['start_date'] . ' 00:00:00', $search['end_date'] . ' 23:59:59']];
        }
                
        // 查询条件
        $total = $this->order->where($w)->count();
        
        $pageInfo = pageCss($total, $page_size);
        $show = $pageInfo['show'];
        $limit = $pageInfo['limit'];
        
        // 获取列表数据
        $sum = $this->order->where($w)->Sum('total_fee');
        $info = $this->order->where($w)->order('create_time desc')->limit($limit)->select();
        foreach ($info as &$value) {
            $value['total_fee'] /= 100;
        }
        
        $max_p = ceil($total / $page_size);
        $max_p = $max_p < 1 ? 1 : $max_p;
        $p = $p > $max_p ? $max_p : $p;
        
        $this->assign('p', $p);
        $this->assign('page', $show);
        $this->assign('info', $info);
        $this->assign('sum', $sum / 100);
        $this->assign('search', $search);
        $this->display();
    }
    
}
