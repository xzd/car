<?php

$menu_permission = array(
    
    // 首页管理 - 广告管理
    14001 => array(
        'name'       => '广告管理',
        'menu'       => 'index-lists',
        'controller' => 'Index',
        'action'     => array( 'shop', 'lists', 'enterprise', 'save', 'delete', 'upload' )
    ),
    14002 => array(
        'name'       => '合作企业管理',
        'menu'       => 'index-enterprise',
        'controller' => 'Index',
        'action'     => array( 'shop', 'lists', 'enterprise', 'save', 'delete', 'upload' )
    ),
    14003 => array(
        'name'       => '商家管理',
        'menu'       => 'index-shop',
        'controller' => 'Index',
        'action'     => array( 'shop', 'lists', 'enterprise', 'save', 'delete', 'upload' )
    ),

    // 运费管理 - 管理
    15001 => array(
        'name'       => '公路运费',
        'menu'       => 'freights-highway',
        'controller' => 'Freights',
        'action'     => array( 'highway', 'index', 'save', 'delete', 'sites' )
    ),
    15002 => array(
        'name'       => '铁路运费',
        'menu'       => 'freights-railway',
        'controller' => 'Freights',
        'action'     => array( 'railway', 'index', 'save', 'delete', 'sites' )
    ),
    15003 => array(
        'name'       => '集装箱运费',
        'menu'       => 'freights-container',
        'controller' => 'Freights',
        'action'     => array( 'container', 'index', 'save', 'delete', 'sites' )
    ),
    15004 => array(
        'name'       => '里程税',
        'menu'       => 'freights-mileage',
        'controller' => 'Freights',
        'action'     => array( 'mileage', 'index', 'save', 'delete', 'sites' )
    ),
    15005 => array(
        'name'       => '站点管理',
        'menu'       => 'freights-site',
        'controller' => 'Freights',
        'action'     => array( 'site', 'ssite', 'dsite' )
    ),
    
    // 内容管理
    10001 => array(
        'name'       => '货源信息',
        'menu'       => 'goods-index',
        'controller' => 'Goods',
        'action'     => array( 'index', 'add', 'delete', 'citys' )
    ),
    
    // 注册用户管理 - 发货方用户管理
    11001 => array(
        'name'       => '发货方用户管理－查看',
        'menu'       => 'users-index',
        'controller' => 'Users',
        'action'     => array( 'index' )
    ),
    11002 => array(
        'name'       => '发货方用户管理－编辑',
        'menu'       => 'users-index',
        'controller' => 'Users',
        'action'     => array( 'update' )
    ),
    11003 => array(
        'name'       => '发货方用户管理－封禁',
        'menu'       => 'users-index',
        'controller' => 'Users',
        'action'     => array( 'lock' )
    ),
    11004 => array(
        'name'       => '发货方用户管理－解封',
        'menu'       => 'users-index',
        'controller' => 'Users',
        'action'     => array( 'unlock' )
    ),
    
    // 注册用户管理 - 车主用户管理
    11005 => array(
        'name'       => '车主用户管理－查看',
        'menu'       => 'users-car',
        'controller' => 'Users',
        'action'     => array( 'car' )
    ),
    11006 => array(
        'name'       => '车主用户管理－封禁',
        'menu'       => 'users-car',
        'controller' => 'Users',
        'action'     => array( 'lock' )
    ),
    11007 => array(
        'name'       => '车主用户管理－解封',
        'menu'       => 'users-car',
        'controller' => 'Users',
        'action'     => array( 'unlock' )
    ),
    
    // 注册用户管理 - 审核管理
    11008 => array(
        'name'       => '审核管理',
        'menu'       => 'users-audit',
        'controller' => 'Users',
        'action'     => array( 'audit', 'audited', 'check' )
    ),
    
    // 注册用户管理 - 新增会员免费管理
    11009 => array(
        'name'       => '新增会员免费管理',
        'menu'       => 'users-free',
        'controller' => 'Users',
        'action'     => array( 'free' )
    ),
    
    
    // 数据统计查询 - 会员数据查询
    12001 => array(
        'name'       => '会员数据查询',
        'menu'       => 'stat-index',
        'controller' => 'Stat',
        'action'     => array( 'index' )
    ),
    
    // 数据统计查询 - 注册用户数据查询
    12002 => array(
        'name'       => '注册用户数据查询',
        'menu'       => 'stat-register',
        'controller' => 'Stat',
        'action'     => array( 'register' )
    ),
    
    // 数据统计查询 - 会员收益查询
    12003 => array(
        'name'       => '会员收益查询',
        'menu'       => 'stat-earnings',
        'controller' => 'Stat',
        'action'     => array( 'earnings' )
    ),
    
    
    // 系统管理 - 角色管理
    13001 => array(
        'name'       => '角色管理',
        'menu'       => 'manage-index',
        'controller' => 'Manage',
        'action'     => array( 'index', 'saveRole', 'deleteRole', 'savePermission' )
    ),
    
    // 系统管理 - 用户管理
    13002 => array(
        'name'       => '用户管理',
        'menu'       => 'manage-admin',
        'controller' => 'Manage',
        'action'     => array( 'admin', 'saveAdmin', 'deleteAdmin' )
    ),
    
    // 系统管理 - 日志管理
    13003 => array(
        'name'       => '日志管理',
        'menu'       => 'manage-log',
        'controller' => 'Manage',
        'action'     => array( 'log' )
    )
    
);

$menuList = array(  
    // 首页管理
    'index-lists' => array(
        'group' => '首页管理',
        'name'  => '广告管理',
        'url'   => '/admin/index/lists/type/1',
        'order' => 1
    ),
    'index-enterprise' => array(
        'group' => '首页管理',
        'name'  => '合作企业管理',
        'url'   => '/admin/index/enterprise/type/2',
        'order' => 2
    ),
    'index-shop' => array(
        'group' => '首页管理',
        'name'  => '商家管理',
        'url'   => '/admin/index/shop/type/4',
        'order' => 3
    ),
    
    // 运费管理
    'freights-highway' => array(
        'group' => '运费管理',
        'name'  => '公路运费',
        'url'   => '/admin/freights/highway/type/1',
        'order' => 1
    ),
    'freights-railway' => array(
        'group' => '运费管理',
        'name'  => '铁路运费',
        'url'   => '/admin/freights/railway/type/2',
        'order' => 2
    ),
    'freights-container' => array(
        'group' => '运费管理',
        'name'  => '集装箱运费',
        'url'   => '/admin/freights/container/type/3',
        'order' => 3
    ),
    'freights-mileage' => array(
        'group' => '运费管理',
        'name'  => '里程税',
        'url'   => '/admin/freights/mileage/type/4',
        'order' => 4
    ),
    'freights-site' => array(
        'group' => '运费管理',
        'name'  => '站点管理',
        'url'   => '/admin/freights/site',
        'order' => 5
    ),
    
    // 内容管理
    'goods-index' => array(
        'group' => '内容管理',
        'name'  => '货源管理',
        'url'   => '/admin/goods/index',
        'order' => 1
    ),
        
    // 注册用户管理
    'users-index' => array(
        'group' => '注册用户管理',
        'name'  => '发货方用户管理',
        'url'   => '/admin/users/index',
        'order' => 1
    ),
    'users-car' => array(
        'group' => '注册用户管理',
        'name'  => '车主用户管理',
        'url'   => '/admin/users/car',
        'order' => 2
    ),
    'users-audit' => array(
        'group' => '注册用户管理',
        'name'  => '审核管理',
        'url'   => '/admin/users/audit',
        'order' => 3
    ),
    'users-free' => array(
        'group' => '注册用户管理',
        'name'  => '新增会员免费管理',
        'url'   => '/admin/users/free',
        'order' => 4
    ),

    // 数据统计查询
    'stat-index' => array(
        'group' => '数据统计查询',
        'name'  => '会员数据查询',
        'url'   => '/admin/stat/index',
        'order' => 1
    ),
    'stat-register' => array(
        'group' => '数据统计查询',
        'name'  => '注册用户数据查询',
        'url'   => '/admin/stat/register',
        'order' => 2
    ),
    'stat-earnings' => array(
        'group' => '数据统计查询',
        'name'  => '会员收益查询',
        'url'   => '/admin/stat/earnings',
        'order' => 3
    ),

    // 系统管理
    'manage-index' => array(
        'group' => '系统管理',
        'name'  => '角色管理',
        'url'   => '/admin/manage/index',
        'order' => 1
    ),
    'manage-admin' => array(
        'group' => '系统管理',
        'name'  => '用户管理',
        'url'   => '/admin/manage/admin',
        'order' => 2
    ),
    'manage-log' => array(
        'group' => '系统管理',
        'name'  => '日志管理',
        'url'   => '/admin/manage/log',
        'order' => 3
    )
);

// 不进菜单的权限点
$not_in_menu = array(11002, 11003, 11004, 11006, 11007);
