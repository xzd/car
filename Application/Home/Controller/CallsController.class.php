<?php

#-------------------------------------------------------------------------------
# Purpose:     联系记录接口
# Author:      xiezhongda@gmail.com
# Created:     2017-09-13
# Updated:     2017-09-13
#-------------------------------------------------------------------------------

namespace Home\Controller;

use Home\Controller\ParentController;

class CallsController extends ParentController {
    
    protected $model;

    public function __construct() {
        parent::__construct();
        
        $this->model = M('calls');
    }
    
    /**
     * 新增联系记录接口
     */
    public function add() {
        $good_id = I('post.good_id', 0);
        if (!is_numeric($good_id)) {
            error(412, '参数错误');
        }
        
        // 非车主会员不用记录
        if ($this->user_info['type'] != 2 || $this->user_info['state'] != 3 || $this->user_info['vip'] != 1) {
            success();
        }
        
        $data = [
            'user_id' => $this->user_info['id'],
            'good_id' => $good_id
        ];
        
        // 已存在不重复记录
        $call = $this->model->where($data)->find();
        if ($call) {
            success();
        }
        
        $res = $this->model->add($data);
        if ($res) {
            success();
        } else {
            error(400, '操作失败，请重试！');
        }
    }
    
}