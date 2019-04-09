<?php

#-------------------------------------------------------------------------------
# Purpose:     登录
# Author:      xiezhongda@gmail.com
# Created:     2017-08-12
# Updated:     2017-08-12
#-------------------------------------------------------------------------------

namespace Admin\Controller;

use Think\Controller;

class LoginController extends Controller {
    
    protected $model;
    protected $is_mobile;


    public function __construct() {
        parent::__construct();
        
        $this->model = M('admins');
        $this->is_mobile = isMobile();
    }

    // 
    public function index() { 
        $this->admin = session('by_user');
        if ($this->admin && $this->is_mobile) {
            redirect('/admin/goods/add');
        }
        
        $this->assign('wap', $this->is_mobile);
        $this->display();
    }
    
    /**
     * 用户登入
     */
    public function signin() {
        $username = I('post.username');
        $password = I('post.password');
        
        if (empty($username) || empty($password)) {
            error(400, '请输入用户名和密码!');
        }
        
        // 获取用户信息
        $user = $this->model->where("username='%s' and state = 1", [$username])->find();

        // 判断用户名是否被注册
        if (!$user) {
            error(400, '用户不存在或被锁定');
        }

        // 验证密码
        if (!password_verify($password, $user['password'])) {
            error(400, '用户名或密码错误');
        }
        
        // 密码正确
        unset($user['password']);
        session('by_user', $user);
        
        addLog($user['id'], $user['username'], '帐号管理', '登录', '登录成功： ' . $user['username']);
        
        
        require APP_PATH . 'Admin/Common/menu_permission.php';
        
        // 获取用户权限 跳转到有权限的第一个菜单
        $permissions = M('roles')->where('id=%d', [$user['role_id']])->getField('permissions');
        if ($this->is_mobile) {
            success('/admin/goods/add');
        } elseif ($permissions == 1) {
            success('/admin/goods/index');
        } else {
            $userPermissions = explode(',', $permissions);
            
            foreach ($userPermissions as $permission) {
                if (!in_array($permission, $not_in_menu)) {
                    success($menuList[$menu_permission[$permission]['menu']]['url']);
                }
            }
        }
    }
    
    /**
     * 退出
     */
    public function signout() {
        session('by_user', null);
        $this->redirect('/admin/login/index');
    }
    
    /**
     * 无权限
     */
    public function noAccess() {
        die('无权限');
    }
    
}