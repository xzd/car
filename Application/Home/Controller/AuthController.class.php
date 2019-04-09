<?php

#-------------------------------------------------------------------------------
# Purpose:     微信网页授权
# Author:      xiezhongda@gmail.com
# Created:     2017-08-05
# Updated:     2017-08-05
#-------------------------------------------------------------------------------

namespace Home\Controller;

use Think\Controller;
use Home\Controller\WechatController;
use EasyWeChat\Foundation\Application;

class AuthController extends Controller {
    
    private $_wx_menu_goods;
    private $_wx_menu_cars;
    
    public function __construct() {
        parent::__construct();
        
        // 回调地址
        $redirect_uri = urlencode(APP_HOME . '/auth/index/type/');
        
        $this->_wx_menu_index = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . C('APP_ID') . '&redirect_uri=' . $redirect_uri . '3&response_type=code&scope=snsapi_userinfo&state=1#wechat_redirect';
        $this->_wx_menu_goods = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . C('APP_ID') . '&redirect_uri=' . $redirect_uri . '1&response_type=code&scope=snsapi_userinfo&state=1#wechat_redirect';
        $this->_wx_menu_cars = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . C('APP_ID') . '&redirect_uri=' . $redirect_uri . '2&response_type=code&scope=snsapi_userinfo&state=1#wechat_redirect';
    }
    
    public function index() {
        $code = I('code');    // 换取 access_token 的票据
        $type = I('type', 1);    // 用户类型, 1为发货放 2为车主
        
        // 错误重新授权地址
        if ($type == 1) {
            $redirect_url = $this->_wx_menu_goods;
        } elseif ($type == 2) {
            $redirect_url = $this->_wx_menu_cars;
        } elseif ($type == 3) {
            $redirect_url = $this->_wx_menu_index;
        }
        
        if (!$code || !in_array($type, [1, 2, 3])) {
            redirectTo($redirect_url);
        }
        
        // 获取 access_token
        $wx_access_token_api = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . C('APP_ID') . '&secret=' . C('APP_SECRET') . '&code=' . $code . '&grant_type=authorization_code';
        $access_token = json_decode(httpsRequest($wx_access_token_api), true);
        if (!isset($access_token['access_token'])) {
            redirectTo($redirect_url);
        }
        
        // 获取微信用户信息
        $wx_get_user_info = 'https://api.weixin.qq.com/sns/userinfo?access_token=' . $access_token['access_token'] . '&openid=' . $access_token['openid'] . '&lang=zh_CN';
        $wx_user_info = json_decode(httpsRequest($wx_get_user_info), true);
        $uid = $this->_setWxUserInfo($wx_user_info);
        if (!$uid) {
            redirectTo($redirect_url);
        } else {
            // 将当前微信 openid 存入 session
            session(WX_USER_SESSION, $access_token['openid']);
            
            if ($type == 1) {
                // 跳转到发货方资源发布页面
                redirectTo(APP_HOME . '/Deliver/add.html');
            } elseif ($type == 2) {
                // 跳转到车主端货源列表页面
                redirectTo(APP_HOME . '/Carowner/lists.html');
            } elseif ($type == 3) {
                // 跳转首页
                redirectTo(APP_HOME . '/Carowner/newIndex.html');
            }
        }
    }
    
    /**
     * 微信事件处理
     */
    public function location() {
        // 
        $app = new Application(C('OPTIONS'));
        
        // 从项目实例中得到服务端应用实例。
        $server = $app->server;
                
        $server->setMessageHandler(function ($message) {
            // 
            if ($message->MsgType == 'event' && $message->Event == 'subscribe') {
                // 用户关注
                return '你好，欢迎关注车来货往！';
            } elseif ($message->MsgType == 'event' && $message->Event == 'LOCATION') {
                // 上报地理位置
                S('location_' . $message->FromUserName, json_encode($message), 864000);
                //return '上报地理位置';
            } else {
                // 其他消息
                return '您的回复我们已收到';
            }
        });
        
        $response = $server->serve();
        
        // 将响应输出
        $response->send();
    }
    
    public function menu() {
        $app = new Application(C('OPTIONS'));
        $menu = $app->menu;
        
        $redirect_uri = urlencode(APP_HOME . '/auth/index/type/');
        $buttons = [
            [
                "type" => "view",
                "name" => "发货方",
                "url"  => "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . C('APP_ID') . "&redirect_uri=" . $redirect_uri . "1&response_type=code&scope=snsapi_userinfo&state=1#wechat_redirect"
            ],
            [
                "type" => "view",
                "name" => "首页",
                "url"  => "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . C('APP_ID') . "&redirect_uri=" . $redirect_uri . "3&response_type=code&scope=snsapi_userinfo&state=1#wechat_redirect"
            ],
            [
                "type" => "view",
                "name" => "司机方",
                "url"  => "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . C('APP_ID') . "&redirect_uri=" . $redirect_uri . "2&response_type=code&scope=snsapi_userinfo&state=1#wechat_redirect"
            ],
        ];
        $res = $menu->add($buttons);
        success($res);
    }
    
    /**
     * 初始化微信用户信息
     * @param type $user_info
     * @return type
     */
    private function _setWxUserInfo($user_info) {
        $User = M('users');
        
        // 判断用户是否存在
        $user = $User->where("open_id='%s'", array($user_info['openid']))->find();
        if (!isset($user['id'])) {
            // 新增用户
            $user['type'] = 0;
            $user['state'] = 1;
            $user['open_id'] = $user_info['openid'];
            $user['nickname'] = $user_info['nickname'];
            $user['header_img'] = $user_info['headimgurl'];
            $user['create_time'] = date('Y-m-d H:i:s');
            $user['id'] = $User->add($user);
        }

        return $user['id'];
    }

}
