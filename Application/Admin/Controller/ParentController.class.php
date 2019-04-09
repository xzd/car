<?php

#-------------------------------------------------------------------------------
# Purpose:     父类
# Author:      xiezhongda@gmail.com
# Created:     2017-08-12
# Updated:     2017-08-12
#-------------------------------------------------------------------------------

namespace Admin\Controller;

use Think\Controller;

class ParentController extends Controller {

    protected $admin;
    protected $menu;
    protected $userPermissions;

    public function __construct() {
        parent::__construct();
        
        $this->admin = session('by_user');
        if (!$this->admin || !isset($this->admin['id'])) {
            redirect('/admin/login/index');
        }
        
        // 权限信息
        require APP_PATH . 'Admin/Common/menu_permission.php';
        
        // 获取用户权限
        $permissions = M('roles')->where('id=%d', [$this->admin['role_id']])->getField('permissions');
        if ($permissions == 1) {
            $this->userPermissions = array_keys($menu_permission);
        } else {
            $this->userPermissions = explode(',', $permissions);
        }

        $menu_had = $menu_arr = $controller_arr = [];
        foreach ($this->userPermissions as $permission_id) {
            // 获取权限信息
            $permission_info = $menu_permission[$permission_id];
            if (!$permission_info['controller']) continue;

            if (!in_array($permission_info['menu'], $menu_had) && !in_array($permission_id, $not_in_menu)) {

                $menus = explode('-', $permission_info['menu']);
                $menu_info = $menuList[$permission_info['menu']];

                if (!$menu_info) continue;
                
                // 菜单详情
                if (!$menu_arr[$menus[0]][$menu_info['order']]) {
                    // 
                    $menu_arr[$menus[0]][$menu_info['order']] = array(
                        'name'    => $menu_info['name'],
                        'view_id' => $menus[1],
                        'url'     => $menu_info['url']
                    );
                }

                // 已保存的菜单
                $menu_had[] = $permission_info['menu'];
            }

            foreach ($permission_info['action'] as $action) {
                if (!in_array($action, $controller_arr[$permission_info['controller']])) {
                    $controller_arr[$permission_info['controller']][] = $action;
                }
            }
        }

        foreach ($menu_arr as $key => $value) {
            ksort($value);
            $menu_arr[$key] = array_values($value);
        }
        $this->menu = $menu_arr;
        //echo json_encode($this->menu);exit;

        if (!in_array(ACTION_NAME, $controller_arr[CONTROLLER_NAME])) {
            $this->redirect('admin/login/noAccess');
        }
                        
        $this->assign('menulist', $this->menu);
        $this->assign('username', $this->admin['username']);
        $this->assign('controller', strtolower(CONTROLLER_NAME));
        $this->assign('action', strtolower(ACTION_NAME));
    }

}
