<?php

namespace Home\Controller;


use Home\Controller\ParentController;
use EasyWeChat\Payment\Order;
use EasyWeChat\Foundation\Application;

/*
 * 发货端控制器
 */

class DeliverController extends ParentController
{
    
    public function __construct() {
        parent::__construct();
                
        $start = I('start', '');
        $start_name = I('start_name', '');
        $end = I('end', '');
        $end_name = I('end_name', '');
                
        // 获取当前定位地址
        if (isset($start) && $start) {
            $s = [
                'province'      => '',
                'city'          => '',
                'district'      => $start,
                'province_name' => '',
                'city_name'     => '',
                'district_name' => $start_name
            ];
        } else {
            if (ACTION_NAME == 'find') {
                $s = [
                    'province'      => '',
                    'city'          => '',
                    'district'      => '',
                    'province_name' => '',
                    'city_name'     => '',
                    'district_name' => '出发地'
                ];
            } else {
                $s = getLocation($this->user_info['open_id']);
            }
        }

        if (isset($end) && $end) {
            $e = [
                'province'      => '',
                'city'          => '',
                'district'      => $end,
                'province_name' => '',
                'city_name'     => '',
                'district_name' => $end_name
            ];
        } else {
            $e = [
                'province'      => '',
                'city'          => '',
                'district'      => '',
                'province_name' => '',
                'city_name'     => '',
                'district_name' => '目的地'
            ];
        }

        $app = new Application(C('OPTIONS'));
        $js = $app->js;
        $this->assign('js', $js);

        $this->assign('start', $s);
        $this->assign('end', $e);
    }

    /*
     * 发货端个人中心
     */
    public function index()
    {
        $this->display();
    }

    /*
    * 发货端找车
    */
    public function find()
    {
        $this->display();
    }


    /*
    * 发货端发货
    */
    public function add()
    {
        $my_vip = $this->user_info['vip'];
        $my_id_no = $this->user_info['id_no'];
        
        $this->assign('my_vip', $my_vip);
        $this->assign('my_id_no', $my_id_no);
        $this->display();
    }

    /*
     * 发货端手机验证
     */
    public function checkMobile()
    {
        //如果没有注册发货端

        if ($this->user_info['type'] != 1) {
            $this->redirect('Deliver/index');
        }
        $this->display();
    }

    /*
  * 发货端修改手机
  */
    public function editMobile()
    {
        //如果没有注册发货端

        if ($this->user_info['type'] != 1) {
            $this->redirect('Deliver/index');
        }
        $this->display();
    }

    /*
     * 发货端注册
     */
    public function regist()
    {
        $car_number_plate_type = $this->user_info['car_number_plate_type'];
        if($car_number_plate_type == 1){
            $this->user_info['car_number_plate_type_name'] = '黄色车牌';
        }elseif($car_number_plate_type == 2){
            $this->user_info['car_number_plate_type_name'] = '蓝色车牌';
        }else{
            $this->user_info['car_number_plate_type_name'] = '';
        }
        $this->assign('user', $this->user_info);
        $this->assign('agent', $this->getOS());
        $this->display();
    }
    private function getOS()
    {
        $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
        if(strpos($agent, 'windows nt')) {
            $platform = 'windows';
        } elseif(strpos($agent, 'macintosh')) {
            $platform = 'mac';
        } elseif(strpos($agent, 'ipod')) {
            $platform = 'ipod';
        } elseif(strpos($agent, 'ipad')) {
            $platform = 'ipad';
        } elseif(strpos($agent, 'iphone')) {
            $platform = 'iphone';
        } elseif (strpos($agent, 'android')) {
            $platform = 'android';
        } elseif(strpos($agent, 'unix')) {
            $platform = 'unix';
        } elseif(strpos($agent, 'linux')) {
            $platform = 'linux';
        } else {
            $platform = 'other';
        }
        return $platform;
    }

    /*
    * 发货端--开通会员
    */
    public function member()
    {
        $my_vip = $this->user_info['vip'];
        $my_id_no = $this->user_info['id_no'];
        
        $app = new Application(C('OPTIONS'));
        
        $json = "''";
        $info = '发货方会员';
        $total_fee = 1;
        $out_trade_no = '1' . time() . rand(100000, 999999);
        $attributes = [
            'trade_type'       => 'JSAPI', // JSAPI，NATIVE，APP...
            'body'             => $info,
            'detail'           => $info,
            'out_trade_no'     => $out_trade_no,
            'total_fee'        => $total_fee, // 单位：分
            'notify_url'       => APP_HOME . '/pay/notify', // 支付结果通知网址，如果不设置则会使用配置里的默认地址
            'openid'           => $this->user_info['open_id'], // trade_type=JSAPI，此参数必传，用户在商户appid下的唯一标识，
        ];
        
        $order = new Order($attributes);
        
        $result = $app->payment->prepare($order);
        if ($result->return_code == 'SUCCESS' && $result->result_code == 'SUCCESS'){
            $prepay_id = $result->prepay_id;
            
            // 返回 json 字符串，如果想返回数组，传第二个参数 false
            $json = $app->payment->configForPayment($prepay_id); 
            
            // 创建订单
            $data = [
                'trade_type'   => 'JSAPI',
                'body'         => $info,
                'out_trade_no' => $out_trade_no,
                'total_fee'    => $total_fee,
                'spbill_create_ip' => getClientIp(),
                'product_id' => 1,
                'openid'     => $this->user_info['open_id'],
                'prepay_id'  => $prepay_id
            ];
            
            M('user_order')->add($data);
        }
        
        $this->assign('my_vip', $my_vip);
        $this->assign('my_id_no', $my_id_no);
        $this->assign('jsApiParameters', $json);
        $this->display();
    }

    /*
      * 发货端 已关闭货源
      */
    public function closed()
    {
        //如果不是vip直接跳转
//        if (!$this->user_info['vip']) {
//            $this->redirect('Deliver/index');
//        }
        $this->display();
    }

    /*
      * 发货端 已发布货源
      */
    public function published()
    {
        //如果不是vip直接跳转
//        if (!$this->user_info['vip']) {
//            $this->redirect('Deliver/index');
//        }
        $this->display();
    }

    /*
     * 发货端车主详情
     */
    public function carowner()
    {
        $id = I('id', 0);
        if ($id <= 0) {
            $this->redirect('Deliver/find');
        }
        $my_vip = $this->user_info['vip'];
        $state = $this->user_info['state'];
        $this->assign('state', $state);
        $this->assign('id', $id);
        $this->assign('my_vip', $my_vip);
        $this->display();
    }


}
