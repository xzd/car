# 项目说明

车来货往项目是车主货主物流信息匹配的平台，平台共分为两部分，即微信端（发货方 + 车主方）和 Web 端后台管理系统

1. 微信端发货方入口: https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx035d30bc5c6d5968&redirect_uri=http%3A%2F%2Fwww.car.com%2Fauth%2Findex%2Ftype%2F1&response_type=code&scope=snsapi_userinfo&state=1#wechat_redirect
2. 微信端车主方入口: https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx035d30bc5c6d5968&redirect_uri=http%3A%2F%2Fwww.car.com%2Fauth%2Findex%2Ftype%2F2&response_type=code&scope=snsapi_userinfo&state=1#wechat_redirect

3. 后台管理系统入口: http://www.car.com/admin
4. 后台管理员初始帐号: admin
5. 后台管理员初始密码: ******（请联系开发人员获取, 登录后请及时修改）

# 技术说明

本项目部署于阿里云 ECS 云服务器, 详情说明如下

1. 后端技术架构: CentOS 7.2 + Nginx 1.10.2 + MySQL 5.7.19 + PHP 6.5.31 + Composer 1.0.1 + ThinkPHP 3.2.3 
2. ThinkPHP 官方文档: https://www.kancloud.cn/manual/thinkphp/1678

# 第三方服务说明

1. 短信服务: 阿里大于 SDK, 加载方法: composer require yuanchao/aliyun-dysms-php-sdk
2. 微信 SDK: EasyWeChat, 加载方法: composer require "overtrue/wechat"

# 服务启动说明

1. PHP 启动/重载/停止 service php-fpm start / reload / stop
2. mysql 启动/停止 service mysqld start / stop
3. nginx 启动/停止 service nginx start / stop


