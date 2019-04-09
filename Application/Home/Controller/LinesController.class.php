<?php

#-------------------------------------------------------------------------------
# Purpose:     常跑路线接口
# Author:      xiezhongda@gmail.com
# Created:     2017-08-09
# Updated:     2017-08-09
#-------------------------------------------------------------------------------

namespace Home\Controller;

use Home\Controller\ParentController;

class LinesController extends ParentController {
    
    protected $model;
    
    public function __construct() {
        parent::__construct();
        
        $this->model = M('lines');
        
        // 车主会员验证
        if ($this->user_info['type'] != 2 || $this->user_info['state'] != 3) {
            error(401, '您尚未成为车主会员，无法使用该功能，请先成为会员');
        }
    }
    
    /**
     * 常跑路线管理页列表接口
     */
    public function index() {
        // 
        $lines = $this->model->where('user_id=%d', [$this->user_info['id']])->order('create_time desc')->select();
        foreach ($lines as &$line) {
            // 
            $totel = $this->_getNotice($line);
            $line['notice'] = $totel;
        }
        
        success($lines);
    }
    
    /**
     * 货源提醒
     */
    public function notice() {
        $notice = 0;
        $lines = $this->model->where('user_id=%d', [$this->user_info['id']])->select();
        foreach ($lines as $line) {
            // 
            $totel = $this->_getNotice($line);
            $notice += $totel;
        }
        
        success($notice);
    }
    
    /**
     * 获取符合常跑路线的货源数
     * @param type $line
     * @return type
     */
    private function _getNotice($line) {
        $where = [
            'start_province' => $line['start_province'],
            'start_city'     => $line['start_city'],
            'end_province'   => $line['end_province'],
            'end_city'       => $line['end_city'],
            'state'          => 1
        ];
        
        if ($line['start_district']) {
            $where['start_district'] = $line['start_district'];
        }
        if ($line['end_district']) {
            $where['end_district'] = $line['end_district'];
        }   

        // 已读提示
        $good_ids = M('reads')->where('user_id=%d', [$this->user_info['id']])->getField('good_id', true);
        if ($good_ids) {
            $where['id'] = ['not in', $good_ids];
        }
        $totel = M('goods')->where($where)->count();
        
        return $totel;
    }

    /**
     * 新增常跑路线
     */
    public function add() {
        // 
        $params['user_id'] = $this->user_info['id'];
        $params['user_open_id'] = $this->user_info['open_id'];
        $params['start_province'] = I('post.start_province');
        $params['start_city'] = I('post.start_city');
        $params['start_district'] = I('post.start_district');
        $params['start_province_name'] = I('post.start_province_name');
        $params['start_city_name'] = I('post.start_city_name');
        $params['start_district_name'] = I('post.start_district_name');
        $params['end_province'] = I('post.end_province');
        $params['end_city'] = I('post.end_city');
        $params['end_district'] = I('post.end_district');
        $params['end_province_name'] = I('post.end_province_name');
        $params['end_city_name'] = I('post.end_city_name');
        $params['end_district_name'] = I('post.end_district_name');
                
        if (!$params['start_province'] || !$params['end_province']
                || !$params['start_city'] || !$params['end_city']) {
            error(412, '请选择完整出发地/目的地！');
        }
        
        $line = $this->model->where($params)->find();
        if ($line) {
            error(400, '已存在相同常跑路线，请勿重复提交！');
        }
        
        $res = $this->model->add($params);
        if ($res) {
            success();
        } else {
            error(400, '新增失败，请重试！');
        }
    }
    
    /**
     * 更新常跑路线
     */
    public function update() {
        // 
        $id = I('post.id');
        if (!$id) {
            error(412, '参数错误！');
        }
        
        $params['user_id'] = $this->user_info['id'];
        $params['start_province'] = I('post.start_province');
        $params['start_city'] = I('post.start_city');
        $params['start_district'] = I('post.start_district');
        $params['start_province_name'] = I('post.start_province_name');
        $params['start_city_name'] = I('post.start_city_name');
        $params['start_district_name'] = I('post.start_district_name');
        $params['end_province'] = I('post.end_province');
        $params['end_city'] = I('post.end_city');
        $params['end_district'] = I('post.end_district');
        $params['end_province_name'] = I('post.end_province_name');
        $params['end_city_name'] = I('post.end_city_name');
        $params['end_district_name'] = I('post.end_district_name');
                
        if (!$params['start_province'] || !$params['end_province']) {
            error(412, '请选择出发地/目的地！');
        }
        
        $line = $this->model->where('id=%d', [$id])->find();
        if (!isset($line['id']) || $line['user_id'] != $this->user_info['id']) {
            error(401, '非法操作！');
        }
        
        $res = $this->model->where('id=%d', [$line['id']])->save($params);
        if ($res) {
            success();
        } else {
            error(400, '更新失败，请重试！');
        }
    }
    
    /**
     * 删除常跑路线
     */
    public function delete() {
        $id = I('post.id');
        if (!$id) {
            error(412, '参数错误');
        }

        $line = $this->model->where('id=%d', [$id])->find();
        if (!isset($line['id']) || $line['user_id'] != $this->user_info['id']) {
            error(401, '非法操作！');
        }
        
        $res = $this->model->where('id=%d', [$line['id']])->delete();
        if ($res) {
            success();
        } else {
            error(400, '删除失败，请重试！');
        }
    }
    
}
