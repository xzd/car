<?php

#-------------------------------------------------------------------------------
# Purpose:     父类
# Author:      xiezhongda@gmail.com
# Created:     2017-08-05
# Updated:     2017-08-05
#-------------------------------------------------------------------------------

namespace Home\Controller;

use Think\Controller;

class ParentController extends Controller {

    protected $open_id;
    protected $user_info;


    public function __construct() {
        parent::__construct();
        
        $this->open_id = session(WX_USER_SESSION);
        if (!$this->open_id) {
            $redirect_uri = urlencode(APP_HOME . '/auth/index/type/1');
            redirectTo('https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . C('APP_ID') . '&redirect_uri=' . $redirect_uri . '&response_type=code&scope=snsapi_userinfo&state=1#wechat_redirect');
        }
                
        $this->user_info = M('users')->where("open_id='%s'", array($this->open_id))->find();
        if (!$this->user_info['phone']) {
            $this->user_info['phone'] = '';
        }
        if (!$this->user_info['car_load']) {
            $this->user_info['car_load'] = '';
        }
        if ($this->user_info['car_number_plate'] == '-') {
            $this->user_info['car_number_plate'] = '';
        }
        if ($this->user_info['car_lenght'] == '-') {
            $this->user_info['car_lenght'] = '';
        }
        if ($this->user_info['car_type'] == '-') {
            $this->user_info['car_type'] = '';
        }
        
        if ($this->user_info['lock'] == 1) {
            $this->redirect('test/lock');exit;
        }
        
        // 点击数, 每天 24 点过期
        $click_num = S('click_num') ?: 0;
        $expire = strtotime(date('Y-m-d 00:00:00', strtotime('+1 day'))) - time();
        S('click_num', ($click_num + 1), $expire);
    }

}
