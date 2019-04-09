<?php

#-------------------------------------------------------------------------------
# Purpose:     定时脚本
# Author:      xiezhongda@gmail.com
# Created:     2017-09-11
# Updated:     2017-09-11
#-------------------------------------------------------------------------------

namespace Home\Controller;

use Think\Controller;

class CronController extends Controller {
    
    /**
     * 重发所有符合条件的货源
     */
    public function index() {
        // 重发所有符合条件的货源
        $update['repeat'] = ['exp','`repeat` - 1'];   // 重发次数减1
        $update['update_time'] = date('Y-m-d H:i:s'); // 
        
        $time = date('Y-m-d H:i', strtotime('-20 minutes'));
        $res1 = M('goods')->where("`state` = 1 and `repeat` > 0 and `update_time` > '$time:00' and `update_time` <= '$time:59'")->save($update);
        
        // 自动关闭 24 小时前的货源
        $close_time = date('Y-m-d H:i', strtotime('-1 day'));
        $res2 = M('goods')->where("`state` = 1 and `update_time` < '$close_time'")->save(['state' => 2, 'close_time' => date('Y-m-d H:i:s')]);
        
        echo $res1 !== false || $res2 !== false ? 'done' : 'error';
    }
    
}