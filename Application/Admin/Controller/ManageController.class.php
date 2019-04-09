<?php

#-------------------------------------------------------------------------------
# Purpose:     系统管理
# Author:      xiezhongda@gmail.com
# Created:     2017-08-12
# Updated:     2017-08-12
#-------------------------------------------------------------------------------

namespace Admin\Controller;

use Admin\Controller\ParentController;

class ManageController extends ParentController {
    
    protected $roles;
    protected $admin;
    protected $logs;

    public function __construct() {
        parent::__construct();
        
        $this->roles = M('roles');
        $this->admins = M('admins');
        $this->logs = M('logs');
    }
    
    /**
     * 角色管理
     */
    public function index() {
        $infos = $this->roles->select();
        
        // 权限信息
        require APP_PATH . 'Admin/Common/menu_permission.php';
        
        $all_permissions = array();
        foreach ($menu_permission as $key => $permission) {            
            if (!$menuList[$permission['menu']]['group']) continue;

            $all_permissions[$menuList[$permission['menu']]['group']]['permission'][$key] = $permission['name'];
        }
        
        foreach ($infos as &$info) {
            if ($info['permissions'] == 1) {
                $info['permissions'] = array_keys($menu_permission);
            } else {
                $info['permissions'] = explode(',', $info['permissions']);
            }
            
            $info['permissionArr'] = $all_permissions;
            foreach ($info['permissionArr'] as $key => $value) {
                $permissions = array_keys($value['permission']);
                $info['permissionArr'][$key]['is_check'] = true;
                foreach ($permissions as $per) {
                    if (!in_array($per, $info['permissions'])) {
                        $info['permissionArr'][$key]['is_check'] = false;
                        break;
                    }
                }
            }
        }            
        
        $this->assign('infos', $infos);
        $this->display();
    }
    
    /**
     * 保存权限
     */
    public function savePermission() {
        $id = I('id/d');
        $params['permissions'] = implode(',', I('post.permissions'));
                
        if (!$id || !$params['permissions']) {
            $this->redirect('/admin/manage/index', [], 1, '参数错误');
        }
        
        $role = $this->roles->where('id=%d', [$id])->find();
        if (!$role) {
            $this->redirect('/admin/manage/index', [], 1, '角色不存在');
        }
        
        $res = $this->roles->where('id=%d', [$id])->save($params);
        if ($res) {
            // 记录日志
            addLog($this->admin['id'], $this->admin['username'], '角色管理', '权限设置', "权限设置：{$role['name']}");
            
            $this->redirect('/admin/manage/index', [], 1, '操作成功');
        } else {
            $this->redirect('/admin/manage/index', [], 1, '操作失败');
        }
    }
    
    /**
     * 新增角色
     */
    public function saveRole() {
        $id = I('id/d');
        $params['name'] = I('post.name');
        $params['state'] = I('post.state', 1);
        $params['describe'] = I('post.describe', '');
                
        if (!$params['name'] || !in_array($params['state'], [1, 2])) {
            $this->redirect('/admin/manage/index', [], 1, '参数错误');
        }
        
        if ($id) {
            $res = $this->roles->where('id=%d', [$id])->save($params);
        } else {
            $res = $this->roles->add($params);
        }
        if ($res) {
            // 记录日志
            $action = $id ? '编辑' : '新增';
            addLog($this->admin['id'], $this->admin['username'], '角色管理', $action, "{$action}角色：{$params['name']}");
            
            $this->redirect('/admin/manage/index', [], 0, '操作成功');
        } else {
            $this->redirect('/admin/manage/index', [], 1, '操作失败');
        }
    }
    
    /**
     * 删除角色
     */
    public function deleteRole() {
        $id = I('post.id');
        if (!$id || $id == 1) {
            $this->redirect('/admin/manage/index', [], 1, '参数错误');
        }
        
        $role = $this->roles->where('id=%d', [$id])->find();
        if (!$role) {
            $this->redirect('/admin/manage/index', [], 1, '角色不存在');
        }
        
        $res = $this->roles->where('id=%d', [$id])->delete();
        if ($res) {
            // 记录日志
            addLog($this->admin['id'], $this->admin['username'], '角色管理', '删除', "删除角色：{$role['name']}");
            
            $this->redirect('/admin/manage/index', [], 1, '操作成功');
        } else {
            $this->redirect('/admin/manage/index', [], 1, '操作失败');
        }
    }
    
    /**
     * 用户管理
     */
    public function admin() {
        $infos = $this->admins->select();
        $roles = $this->roles->where('state=1')->getField('id,name');
        foreach ($infos as &$info) {
            $info['role'] = $roles[$info['role_id']];
        }
        
        $this->assign('roles', $roles);
        $this->assign('infos', $infos);
        $this->display();
    }
    
    /**
     * 新增管理员
     */
    public function saveAdmin() {
        $id = I('id/d', '');
        $params['username'] = I('post.username');
        $params['role_id'] = I('post.role_id');
        $params['state'] = I('post.state', 1);
        $params['password'] = I('post.password', '');
                        
        if ($id == 1) {
            $params['username'] = 'admin';
            $params['role_id'] = 1;
            $params['state'] = 1;
        }
        
        if (!$params['username'] || !$params['role_id'] || (!$id && !$params['password']) || !in_array($params['state'], [1, 2])) {
            $this->redirect('/admin/manage/admin', [], 1, '参数错误');
        }
        
        // 
        if ($params['password']) {
            $params['password'] = password_hash($params['password'], PASSWORD_DEFAULT);
        } else {
            unset($params['password']);
        }
        
        if ($id) {
            $res = $this->admins->where('id=%d', [$id])->save($params);
        } else {
            $res = $this->admins->add($params);
        }
        if ($res >= 0) {
            // 记录日志
            $action = $id ? '编辑' : '新增';
            addLog($this->admin['id'], $this->admin['username'], '用户管理', $action, "{$action}用户：{$params['username']}");
            
            $this->redirect('/admin/manage/admin', [], 0, '操作成功');
        } else {
            $this->redirect('/admin/manage/admin', [], 1, '操作失败');
        }
    }
    
    /**
     * 删除管理员
     */
    public function deleteAdmin() {
        $id = I('post.id');
        if (!$id || $id == 1) {
            $this->redirect('/admin/manage/admin', [], 1, '参数错误');
        }
        
        $admin = $this->admins->where('id=%d', [$id])->find();
        if (!$admin) {
            $this->redirect('/admin/manage/admin', [], 1, '用户不存在');
        }
        
        $res = $this->admins->where('id=%d', [$id])->delete();
        if ($res) {
            // 记录日志
            addLog($this->admin['id'], $this->admin['username'], '用户管理', '删除', "删除用户：{$admin['username']}");
            
            $this->redirect('/admin/manage/admin', [], 1, '操作成功');
        } else {
            $this->redirect('/admin/manage/admin', [], 1, '操作失败');
        }
    }
    
    /**
     * 日志管理
     */
    public function log() {
        $page_size = C('pageSize');
        $p = I('p/d', 1);
        
        if (I('admin_id')) $search['admin_id'] = I('admin_id');
        $search['start_date'] = I('start_date', date('Y-m-01'));
        $search['end_date'] = I('end_date', date('Y-m-d'));
        
        // where(
        $w = [];
        if (isset($search['admin_id'])) {
            $w['admin_id'] = $search['admin_id'];
        }
        if (isset($search['start_date']) && isset($search['end_date'])) {
            $w['create_time'] = ['BETWEEN', [$search['start_date'] . ' 00:00:00', $search['end_date'] . ' 23:59:59']];
        }
        
        $total = $this->logs->where($w)->count();
        
        $pageInfo = pageCss($total, $page_size);
        $show = $pageInfo['show'];
        $limit = $pageInfo['limit'];
        
        // 获取列表数据
        $info = $this->logs->where($w)->order('create_time desc')->limit($limit)->select();
        
        $max_p = ceil($total / $page_size);
        $max_p = $max_p < 1 ? 1 : $max_p;
        $p = $p > $max_p ? $max_p : $p;
        
        // 获取所有用户信息
        $admins = $this->admins->getField('id,username');
        $this->assign('admins', $admins);
        
        $this->assign('p', $p);
        $this->assign('page', $show);
        $this->assign('info', $info);
        $this->assign('search', $search);
        $this->display();
    }
    
}
