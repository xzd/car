<?php

return array(
    //应用设定
    'MODULE_DENY_LIST'  => array('Common', 'Runtime'),
    'MODULE_ALLOW_LIST' => array('Home', 'Admin'), // 配置你原来的分组列表

    //默认设定
    'DEFAULT_MODULE'     => 'Home',   // 默认模块
    'DEFAULT_CONTROLLER' => 'Index',  // 默认控制器名称
    'DEFAULT_ACTION'     => 'index',  // 默认操作名称

    //数据库设置
    'DB_TYPE'    => 'mysql',           // 数据库类型
    'DB_HOST'    => '127.0.0.1',       // 数据库服务器地址
    'DB_NAME'    => 'cars',            // 数据库名称
    'DB_USER'    => 'root',            // 数据库用户名
    'DB_PWD'     => 'secret',          // 数据库密码
    'DB_PORT'    => 3306,              // 数据库端口
    'DB_PREFIX'  => 'car_',            // 数据表默认前缀
    'DB_CHARSET' => 'utf8mb4',
    
    // 缓存 设置
    'DATA_CACHE_TYPE'     => 'memcache',
    'MEMCACHE_HOST'       => '127.0.0.1',
    'MEMCACHE_PORT'       => '11211',
    'DATA_CACHE_TIME'     => '86400',
    'DATA_CACHE_COMPRESS' => false,

    //SESSION设置
    'SESSION_AUTO_START' => true,

    // 错误信息处理方式（注释掉，则会在页面展示所有错误信息，注意要清缓存）
    //'TMPL_EXCEPTION_FILE' => APP_PATH . 'Home/View/404.html',

    // 是否开启模板编译缓存,设为false则每次都会重新编译
    'TMPL_CACHE_ON' => false,
    'TMPL_CACHE_TIME' => 0,
    
    // 默认参数过滤方法 用于I函数...
    'DEFAULT_FILTER' => 'htmlspecialchars,strip_tags',

    //URL设置
    'URL_MODEL' => 2,
    'URL_CASE_INSENSITIVE' => false,

    //日志设置
    'LOG_RECORD' => true, // 开启日志记录
    'LOG_LEVEL'  => 'EMERG,ALERT,CRIT,ERR,SQL', // 只记录EMERG ALERT CRIT ERR 错误
    
    // 微信配置信息
    'APP_ID' => '',
    'APP_SECRET' => '',
    
    'OPTIONS' => [
        'debug'  => true,
        'app_id' => '',
        'secret' => '',
        'token'  => '',
        // 'aes_key' => null, // 可选
        'log' => [
            'level' => 'debug',
            'file'  => '/var/log/wechat/easywechat.log', // XXX: 绝对路径！！！！
        ],
        /**
         * 微信支付
         */
        'payment' => [
            'merchant_id'        => '',
            'key'                => '',
            'cert_path'          => '/var/www/cars/Application/Common/Pay/apiclient_cert.pem',      // XXX: 绝对路径！！！！
            'key_path'           => '/var/www/cars/Application/Common/Pay/apiclient_key.pem',       // XXX: 绝对路径！！！！
            'notify_url'         => 'http://www.chelaihuowang.xin/pay/notify',                      // 你也可以在下单时单独设置来想覆盖它
            'device_info'        => 'MWEB',
        ],
    ]
);
