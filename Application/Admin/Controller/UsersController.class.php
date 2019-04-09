<?php

#-------------------------------------------------------------------------------
# Purpose:     注册用户管理
# Author:      xiezhongda@gmail.com
# Created:     2017-08-12
# Updated:     2017-08-12
#-------------------------------------------------------------------------------

namespace Admin\Controller;

use Admin\Controller\ParentController;

class UsersController extends ParentController {
    
    protected $model;
    protected $free_model;
    
    protected $show_index_edit = false;
    protected $show_index_lock = false;
    protected $show_index_unlock = false;
    
    protected $show_car_lock = false;
    protected $show_car_unlock = false;
    

    public function __construct() {
        parent::__construct();
        
        if (in_array(11002, $this->userPermissions)) {
            $this->show_index_edit = true;
        }

        if (in_array(11003, $this->userPermissions)) {
            $this->show_index_lock = true;
        }

        if (in_array(11004, $this->userPermissions)) {
            $this->show_index_unlock = true;
        }

        if (in_array(11006, $this->userPermissions)) {
            $this->show_car_lock = true;
        }

        if (in_array(11007, $this->userPermissions)) {
            $this->show_car_unlock = true;
        }
        
        $this->model = M('users');
        $this->free_model = M('vip_free');
    }
    
    /**
     * 发货方用户管理
     */
    public function index() {
        $this->_getUsers(1);
        
        $this->assign('show_index_edit', $this->show_index_edit);
        $this->assign('show_index_lock', $this->show_index_lock);
        $this->assign('show_index_unlock', $this->show_index_unlock);
        $this->display();
    }
    
    /**
     * 车主用户管理
     */
    public function car() {
        $this->_getUsers(2);
        
        $this->assign('show_car_lock', $this->show_car_lock);
        $this->assign('show_car_unlock', $this->show_car_unlock);
        $this->display();
    }
    
    /**
     * 禁封用户
     */
    public function lock() {
        $this->_lockOrUnlock(1);
    }
    
    /**
     * 解封用户
     */
    public function unlock() {
        $this->_lockOrUnlock(0);
    }
    
    /**
     * 待审核
     */
    public function audit() {
        $this->_getUsers(0, 2);
        $this->display();
    }
    
    /**
     * 已审核
     */
    public function audited() {
        $state = I('state', '') ?: [3, 4];
        
        $this->_getUsers(0, $state);
        $this->display();
    }
    
    /**
     * 新增会员免费管理
     */
    public function free() {
        $free = $this->free_model->where('id=1')->find();
        
        if (IS_POST) {
            $update['start'] = I('post.start', 0);
            $update['start_date'] = I('post.start_date');
            $update['end_date'] = I('post.end_date');
            $update['free_month'] = I('post.free_month', 0);
            
            $res = $this->free_model->where('id=1')->save($update);
            if ($res) {
                // 记录日志
                if ($update['start'] == 0) {
                    addLog($this->admin['id'], $this->admin['username'], '新增会员免费管理', '关闭', "关闭新增会员免费功能");
                } else {
                    addLog($this->admin['id'], $this->admin['username'], '新增会员免费管理', '开启', "开启新增会员免费功能, 功能开启期限：{$update['start_date']} - {$update['end_date']}, 会员免费期限：{$update['free_month']} 个月");
                }
                    
                $this->redirect('/admin/users/free', [], 1, '操作成功');
            } else {
                $this->redirect('/admin/users/free', [], 1, '无更新');
            }
        }
        
        $this->assign('info', $free);
        $this->display();
    }
    
    /**
     * 禁封 / 解封用户
     * @param type $lock
     */
    private function _lockOrUnlock($lock) {
        $id = I('id/d');
        $type = I('type/d', 1);

        if (I('nickname')) $search['nickname'] = I('nickname');
        if (I('phone')) $search['phone'] = I('phone');
        
        $redirect = $type == 1 ? '/admin/users/index' : '/admin/users/car';
        
        if (!$id || !$type) {
            $this->redirect($redirect, $search, 1, '参数错误');
        }
        
        // 
        $user = $this->model->where('id=%d', [$id])->find();
        if (!$user) {
            $this->redirect($redirect, $search, 1, '用户不存在');
        }
        
        $update['lock'] = $lock;
        $res = $this->model->where('id=%d', [$id])->save($update);
        if ($res) {
            // 记录日志
            $menu = $type == 1 ? '发货方用户管理' : '车主用户管理';
            $action = $lock == 1 ? '禁封' : '解封';
            addLog($this->admin['id'], $this->admin['username'], $menu, $action, $action . "用户： {$user['name']}（微信昵称：{$user['nickname']}）");
            
            $this->redirect($redirect, $search, 1, '操作成功');
        } else {
            $this->redirect($redirect, $search, 1, '操作失败');
        }
    }

    /**
     * 更新用户类型
     */
    public function update() {
        $id = I('post.id/d');
        $type = I('post.type/d', 1);
        $type_goods = I('post.type_goods/d');

        if (I('nickname')) $search['nickname'] = I('nickname');
        if (I('phone')) $search['phone'] = I('phone');
        
        $redirect = $type == 1 ? '/admin/users/index' : '/admin/users/car';
        
        if (!$id || !$type_goods) {
            $this->redirect($redirect, $search, 1, '参数错误');
        }
        
        // 
        $user = $this->model->where('id=%d', [$id])->find();
        if (!$user) {
            $this->redirect($redirect, $search, 1, '用户不存在');
        }
        
        $res = $this->model->where('id=%d', [$id])->save(['type_goods' => $type_goods]);
        if ($res) {
            // 记录日志
            $menu = $type == 1 ? '发货方用户管理' : '车主用户管理';
            $type_goods_name = $type_goods == 1 ? '货主' : '货站';
            addLog($this->admin['id'], $this->admin['username'], $menu, '编辑', "修改用户： {$user['name']}（微信昵称：{$user['nickname']}）的发货方类型为 $type_goods_name");
            
            $this->redirect($redirect, $search, 1, '操作成功');
        } else {
            $this->redirect($redirect, $search, 1, '操作失败');
        }
    }

    /**
     * 审核用户
     */
    public function check() {
        $id = I('post.id/d');
        $type = I('post.type/d', 1);
        $state = I('post.state/d');
        $remark = I('post.remark');
        
        if (I('nickname')) $search['nickname'] = I('nickname');
        if (I('phone')) $search['phone'] = I('phone');
        
        $redirect = $type == 1 ? '/admin/users/audit' : '/admin/users/audited';
        
        if (!$id || !$type || !in_array($state, [3, 4])) {
            $this->redirect($redirect, $search, 1, '参数错误');
        }
        
        // 
        $user = $this->model->where('id=%d', [$id])->find();
        if (!$user) {
            $this->redirect($redirect, $search, 1, '用户不存在');
        }
        
        $update['state'] = $state;
        $update['remark'] = $remark;
        $update['audit_time'] = date('Y-m-d H:i:s');
        // 审核通过
        if ($state == 3) {
            // 获取会员免费时间
            $now = date('Y-m-d');
            $free = $this->free_model->where('id=1')->find();
            if ($free['start'] == 1 && $now >= $free['start_date'] && $now <= $free['end_date']) {
                $update['vip'] = 1;
                $update['vip_starttime'] = date('Y-m-d H:i:s');
                $update['vip_endtime'] = date('Y-m-d H:i:s', strtotime('+' . $free['free_month'] . ' month'));
            }
        } else {
            $update['vip'] = 0;
        }
        
        $res = $this->model->where('id=%d', [$id])->save($update);
        if ($res) {
            // 记录日志
            $action = $state == 3 ? '审核通过' : '审核驳回';
            $user_type = $user['type'] == 1 ? '发货方' : '车主';
            addLog($this->admin['id'], $this->admin['username'], '审核管理', $action, "{$action} - {$user_type}注册用户：{$user['name']}（微信昵称：{$user['nickname']}）, {$remark}");
            
            // 审核驳回将用户历史货源关闭
            if ($state == 4) {
                $up = ['state' => 2];
            } else {
                $up = [
                    'user_name' => $user['name'],
                    'user_phone' => $user['phone'],
                    'user_id_no' => $user['id_no'],
                    'user_nickname' => $user['nickname'],
                    'user_header_img' => $user['header_img'],
                    'user_type_goods' => $user['type_goods']
                ];
            }
            M('goods')->where('user_id=%d and is_admin=0', [$id])->save($up);
            
            // 发模板消息
            sendAuditMessage($user['open_id'], $user_type, $action, $remark);
            
            $this->redirect($redirect, $search, 1, '操作成功');
        } else {
            $this->redirect($redirect, $search, 1, '操作失败');
        }
    }
    
    /**
     * 获取用户信息
     * @param type $type
     */
    private function _getUsers($type, $state = 3) {
        $page_size = C('pageSize');
        $p = I('p/d', 1);
        
        $search = [];
        if (I('nickname')) $search['nickname'] = I('nickname');
        if (I('phone')) $search['phone'] = I('phone');
        if (I('state')) $search['state'] = I('state');
        
        // where(
        $w = [];
        if ($type) {
            $w['type'] = $type;
        }
        if (is_array($state)) {
            $w['state'] = array('in', $state);
        } elseif ($state) {
            $w['state'] = $state;
        }
        
        if ($search['nickname']) {
            $w['nickname'] = ['like', '%' . $search['nickname'] . '%'];
        }
        if ($search['phone']) {
            $w['phone'] = ['like', '%' . $search['phone'] . '%'];
        }
        
        // 查询条件
        $total = $this->model->where($w)->count();
        
        $pageInfo = pageCss($total, $page_size);
        $show = $pageInfo['show'];
        $limit = $pageInfo['limit'];
        
        // 获取列表数据
        $info = $this->model->where($w)->order('update_time desc')->limit($limit)->select();
        foreach ($info as &$v) {
            $v['vip_starttime'] = $v['vip_starttime'] == '1970-01-01' ? '' : date('Y/m/d', strtotime($v['vip_starttime']));
            $v['vip_endtime'] = $v['vip_endtime'] == '1970-01-01' ? '' : date('Y/m/d', strtotime($v['vip_endtime']));
        }
        
        $max_p = ceil($total / $page_size);
        $max_p = $max_p < 1 ? 1 : $max_p;
        $p = $p > $max_p ? $max_p : $p;
        
        $this->assign('p', $p);
        $this->assign('page', $show);
        $this->assign('info', $info);
        $this->assign('search', $search);
    }
    
}
