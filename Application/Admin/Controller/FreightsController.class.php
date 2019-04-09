<?php

#-------------------------------------------------------------------------------
# Purpose:     运费信息
# Author:      xiezhongda@gmail.com
# Created:     2017-11-05
# Updated:     2017-11-05
#-------------------------------------------------------------------------------

namespace Admin\Controller;

use Admin\Controller\ParentController;

class FreightsController extends ParentController {
    
    protected $model;
    protected $location;
    protected $type;
    protected $type_name;

    public function __construct() {
        parent::__construct();
        
        $this->model = M('freights');
        $this->location = M('freight_locations');
        
        $this->type = I('type', 1);
        
        $type_names = [1 => 'highway', 2 => 'railway', 3 => 'container', 4 => 'mileage'];
        $this->type_name = $type_names[$this->type];
                
        $this->assign('type', $this->type);
    }    
    
    /**
     * 列表页
     */
    public function site() {
        $page_size = C('pageSize');
        $p = I('p/d', 1);
        
        // where(
        $w = ['parent' => ['neq', 0]];
        $total = $this->location->where($w)->count();
        
        $pageInfo = pageCss($total, $page_size);
        $show = $pageInfo['show'];
        $limit = $pageInfo['limit'];
        
        // 获取列表数据
        $info = $this->location->where($w)->order('parent')->order('code')->limit($limit)->select();        
        
        // 获取所以局信息
        $codes = $this->location->where('parent=0')->getField('code,name'); 

        $max_p = ceil($total / $page_size);
        $max_p = $max_p < 1 ? 1 : $max_p;
        $p = $p > $max_p ? $max_p : $p;
        
        $this->assign('p', $p);
        $this->assign('page', $show);
        $this->assign('info', $info);
        $this->assign('codes', $codes);
        $this->display();
    }
    
    public function ssite() {
        if (IS_POST) {
            $params['name'] = I('post.name');
            $params['parent'] = I('post.parent');
                        
            // 获取同局最大ID
            $first = substr($params['parent'], 0, 2);
            $max = $this->location->where('parent=%d', [$params['parent']])->max('code');
            if (!$max) {
                $params['code'] = $first . '0100';
            } else {
                $second = substr($max, 2, 2);
                $num = str_pad((intval($second) + 1), 2, '0', STR_PAD_LEFT);
                $params['code'] = $first . $num . '00';
            }
                        
            $res = $this->location->add($params);  
            
            if ($res !== false) {
                $this->redirect('/admin/freights/site', ['p' => $p], 1, '操作成功');
            } else {
                $this->redirect('/admin/freights/site', ['p' => $p], 1, '操作失败');
            }
        }

        $parents = $this->location->where('parent=0')->select();
        $this->assign('parents', $parents);
        $this->display();
    }
    
    public function dsite() {
        $id = I('id');        
        if (!$id) {
            error();
        }
        
        $location = $this->location->where('id=%d', [$id])->find();
        if (!isset($location['id'])) {
            error(400, '非法操作');
        }

        $res = $this->location->where('id=' . $location['id'])->delete();
        if ($res) {
            // 记录日志
            addLog($this->admin['id'], $this->admin['username'], '运费管理', '删除站点', $id);

            success();
        } else {
            error(400, '操作失败');
        }
    }
    
    
    // 公路运费
    public function highway() {
        $this->lists();
    }
    
    // 铁路运费
    public function railway() {
        $this->lists();
    }
    
    // 集装箱运费
    public function container() {
        $this->lists();
    }
    
    // 里程税
    public function mileage() {
        $this->lists();
    }
    
    /**
     * 列表页
     */
    public function lists() {
        $page_size = C('pageSize');
        $p = I('p/d', 1);
        
        // where(
        $w = ['type' => $this->type];
        $total = $this->model->where($w)->count();
        
        $pageInfo = pageCss($total, $page_size);
        $show = $pageInfo['show'];
        $limit = $pageInfo['limit'];
        
        // 获取列表数据
        $info = $this->model->where($w)->order('create_time desc')->limit($limit)->select();
        foreach ($info as &$value) {
            if ($this->type == 2) {
                $price_info = explode('-', $value['price']);
                $value['price_0'] = $price_info[0];
                $value['price_1'] = $price_info[1];
                $value['price_2'] = $price_info[2];
                $value['price_3'] = $price_info[3];                $value['price_info'] = I('post.price_1', 0) . '-' . I('post.price_2', 0);
            }
        }
        
        
        $max_p = ceil($total / $page_size);
        $max_p = $max_p < 1 ? 1 : $max_p;
        $p = $p > $max_p ? $max_p : $p;
        
        $this->assign('p', $p);
        $this->assign('page', $show);
        $this->assign('info', $info);
        $this->display('lists');
    }
    
    public function save() {
        $id = I('id/d');
        $p = I('p/d', 1);
        
        $redirect = '/admin/freights/' . $this->type_name . '/type/' . $this->type;
        if ($id) {
            $info = $this->model->where('id=%d', [$id])->find();
            if (!$info) {
                $this->redirect($redirect, ['p' => $p], 1, '数据不存在');
            }
            
            if ($this->type == 1) {
                $price_info = explode('-', $info['price']);
                $info['price_0'] = $price_info[0];
                $info['price_1'] = $price_info[1];
            } elseif ($this->type == 2) {
                $price_info = explode('-', $info['price']);
                $info['price_0'] = $price_info[0];
                $info['price_1'] = $price_info[1];
                $info['price_2'] = $price_info[2];
                $info['price_3'] = $price_info[3];
            }
        } else {
            $info = [
                'start_province_name' => '内蒙古',
                'start_province' => '150000',
                'start_city_name' => '呼伦贝尔市',
                'start_city' => '150700',
                'start_district_name' => '满洲里市',
                'start_district' => '150781'
            ];
        }
        
        if (IS_POST) {
            $params['start_province_name'] = I('post.start_province_name');
            $params['end_province_name'] = I('post.end_province_name');
            $params['start_province'] = I('post.start_province');
            $params['end_province'] = I('post.end_province');
            
            $params['start_city_name'] = I('post.start_city_name');
            $params['end_city_name'] = I('post.end_city_name');
            $params['start_city'] = I('post.start_city');
            $params['end_city'] = I('post.end_city');
            
            $params['start_district_name'] = I('post.start_district_name');
            $params['end_district_name'] = I('post.end_district_name');
            $params['start_district'] = I('post.start_district');
            $params['end_district'] = I('post.end_district');
            
            $params['type'] = $this->type;
            
            if ($this->type == 1) {
                $params['price'] = I('post.price_0', 0) . '-' . I('post.price_1', 0);
            } elseif ($this->type == 2) {
                $params['price'] = I('post.price_0', 0) . '-' . I('post.price_1', 0) . '-' . I('post.price_2', 0) . '-' . I('post.price_3', 0);
            } else {
                $params['price'] = I('post.price', 0);
            }
            
            if ($id) {
                $res = $this->model->where('id=%d', [$id])->save($params);  
            } else {
                $res = $this->model->add($params);  
            }
            
            if ($res !== false) {
                $this->redirect($redirect, ['p' => $p], 1, '操作成功');
            } else {
                $this->redirect($redirect, ['p' => $p], 1, '操作失败');
            }
        }
        
        $this->assign('info', $info);
        $this->display();
    }
    
    /**
     * 删除货源
     */
    public function delete() {
        $id = I('id');
        $redirect = '/admin/freights/' . $this->type_name . '/type/' . $this->type;
        
        if (!$id) {
            error();
        }
        
        $freight = $this->model->where('id=%d', [$id])->find();
        if (!isset($freight['id'])) {
            error(400, '非法操作');
        }

        $res = $this->model->where('id=' . $freight['id'])->delete();
        if ($res) {
            // 记录日志
            addLog($this->admin['id'], $this->admin['username'], '运费管理', '删除信息', $id);

            success();
        } else {
            error(400, '操作失败');
        }
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
    
}
