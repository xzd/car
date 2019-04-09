<?php

namespace Home\Controller;

use Think\Controller;

class TestController extends Controller {

    public function lock() {
        $this->display();
    }
    
    /**
     * 模拟登录, web 测试专用
     */
    public function login() {
        $open_id = I('openid');
        
        session(WX_USER_SESSION, $open_id);
    }
    
//    public function mcache() {
//        S('oFQtdwd2EJKQxtyvb3id5JX3n1M1', '{"ToUserName":"gh_adc14d737c41","FromUserName":"oFQtdwd2EJKQxtyvb3id5JX3n1Ms","CreateTime":"1502263517","MsgType":"event","Event":"LOCATION","Latitude":"30.191107","Longitude":"120.197914","Precision":"65.000000"}');
//    }
    
    
//    public function code() {
//        echo S('location_o67l21vm5H1sfNuhQd_g1ePCSLiI');
//    }
    
    public function img() {
        //打开目录
//        $path = './upload/2017-09-19/';
//        $dir = @opendir($path);
//        if (!$dir)
//            return false;
//
//        $res = '';
//        //列出目录中的文件
//        while (($file = readdir($dir)) !== false) {
//            if ($file == '.' || $file == '..')
//                continue;
//            //目录
//            if (is_dir($path . $file)) {
//                $res .= "filename: " . $path . $file . "\n<br>";
//            } else {
//                $res .= "filename: " . $path . $file . "\n<br>";
//                
//            }
//        }
//        closedir($dir);
//        echo $res;
        
        
//        !is_dir('./upload/2017-09-19') && mkdir('./upload/2017-09-19', 0777);
//        
//        $image = new \Think\Image(); 
//        $image->open('./upload/2017-09-19/59c111afc4bc1.jpeg');
//        // 按照原图的比例生成一个最大为150*150的缩略图并保存为thumb.jpg
//        $image->thumb(400, 400)->save('./upload/2017-09-19/59c111afc4bc1_thumb.jpeg');
                
    }
    
}
