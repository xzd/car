<?php

// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// 应用入口文件
// 检测PHP环境
if (version_compare(PHP_VERSION, '5.3.0', '<')) die('require PHP > 5.3.0 !');

require_once './vendor/autoload.php';
require_once './Application/Common/Common/Sms.php';

// 开启调试模式 建议开发阶段开启 部署阶段注释或者设为false
define('APP_DEBUG', true);

// 定义应用目录
define('APP_PATH', './Application/');   

// 常量定义
define('APP_HOME', 'http://www.chelaihuowang.xin');
define('WX_USER_SESSION', 'CURRENT_WECHAT_USER_OPENID');
define('TOKEN', '9EiOmGuUqwP4ZliDC3MXV1QUgLPybYzC');

// 引入ThinkPHP入口文件
require './ThinkPHP/ThinkPHP.php';

// 亲^_^ 后面不需要任何代码了 就是如此简单