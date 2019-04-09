<?php

namespace Home\Controller;

use Home\Controller\ParentController;
use EasyWeChat\Payment\Order;
use EasyWeChat\Foundation\Application;

/*
 * 车主端控制器
 */

class CarownerController extends ParentController {

    public function __construct() {
        parent::__construct();
        
        $start_province = I('sp', '');
        $start_city = I('sc', '');
        $start_district = I('sd', '') ?: $start_city;
        $start_province_name = I('spn', '');
        $start_city_name = I('scn', '');
        $start_district_name = I('sdn', '') ?: $start_city_name;
        
        $end_province = I('ep', '');
        $end_city = I('ec', '');
        $end_district = I('ed', '') ?: $end_city;
        $end_province_name = I('epn', '');
        $end_city_name = I('ecn');
        $end_district_name = I('edn', '') ?: $end_city_name;

        // 获取当前定位地址
        if (isset($start_province) && $start_province) {
            $start = [
                'province'      => $start_province,
                'city'          => $start_city,
                'district'      => $start_district,
                'province_name' => $start_province_name,
                'city_name'     => $start_city_name,
                'district_name' => $start_district_name
            ];
        } else {
            $start = [
                'province'      => '',
                'city'          => '',
                'district'      => '',
                'province_name' => '',
                'city_name'     => '',
                'district_name' => '出发地'
            ];
            //$start = getLocation($this->user_info['open_id']);
        }
        
        // 
        if (isset($end_province) && $end_province) {
            $end = [
                'province'      => $end_province,
                'city'          => $end_city,
                'district'      => $end_district,
                'province_name' => $end_province_name,
                'city_name'     => $end_city_name,
                'district_name' => $end_district_name
            ];
        } else {
            $end = [
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
        
        $this->assign('start', $start);
        $this->assign('end', $end);
    }

    /*
     * 个人中心
     */

    public function index() {
        $this->display();
    }

    /*
     * 常跑路线
     */

    public function route() {

        //如果不是vip直接跳转
//        if (!$this->user_info['vip']) {
//            $this->redirect('Carowner/index');
//        }

        $this->display();
    }

    /*
     * 新增路线
     */

    public function addRoute() {
        //如果不是vip直接跳转
        if (!$this->user_info['vip']) {
            $this->redirect('Carowner/index');
        }

        $this->display();
    }

    public function newIndex() {
        //如果不是vip直接跳转
        $app = new Application(C('OPTIONS'));
        $js = $app->js;
        $this->assign('js', $js);
        $this->display();
    }

    /*
     * 会员权限
     */

    public function member() {
        $app = new Application(C('OPTIONS'));

        $json = "''";
        $info = '车主方会员';
        $total_fee = 1;
        $out_trade_no = '2' . time() . rand(100000, 999999);
        $attributes = [
            'trade_type' => 'JSAPI', // JSAPI，NATIVE，APP...
            'body' => $info,
            'detail' => $info,
            'out_trade_no' => $out_trade_no,
            'total_fee' => $total_fee, // 单位：分
            'notify_url' => APP_HOME . '/pay/notify', // 支付结果通知网址，如果不设置则会使用配置里的默认地址
            'openid' => $this->user_info['open_id'], // trade_type=JSAPI，此参数必传，用户在商户appid下的唯一标识，
        ];

        $order = new Order($attributes);

        $result = $app->payment->prepare($order);
        if ($result->return_code == 'SUCCESS' && $result->result_code == 'SUCCESS') {
            $prepay_id = $result->prepay_id;

            // 返回 json 字符串，如果想返回数组，传第二个参数 false
            $json = $app->payment->configForPayment($prepay_id);

            // 创建订单
            $data = [
                'trade_type' => 'JSAPI',
                'body' => $info,
                'out_trade_no' => $out_trade_no,
                'total_fee' => $total_fee,
                'spbill_create_ip' => getClientIp(),
                'product_id' => 2,
                'openid' => $this->user_info['open_id'],
                'prepay_id' => $prepay_id
            ];

            M('user_order')->add($data);
        }

        $this->assign('jsApiParameters', $json);
        $this->display();
    }

    /*
     * 货源
     */

    public function lists() {
        $this->display();
    }

    /*
     * 验证手机
     */

    public function checkMobile() {

        if ($this->user_info['type'] != 2) {
            $this->redirect('Carowner/index');
        }
        $this->display();
    }

    /*
     * 修改手机
     */

    public function editMobile() {

        if ($this->user_info['type'] != 2) {
            $this->redirect('Carowner/index');
        }
        $this->display();
    }

    public function regist() {
        $car_number_plate_type = $this->user_info['car_number_plate_type'];
        if ($car_number_plate_type == 1) {
            $this->user_info['car_number_plate_type_name'] = '黄色车牌';
        } elseif ($car_number_plate_type == 2) {
            $this->user_info['car_number_plate_type_name'] = '蓝色车牌';
        } else {
            $this->user_info['car_number_plate_type_name'] = '';
        }

        $this->assign('user', $this->user_info);
        $this->assign('agent', $this->getOS());
        $this->display();
    }

    private function getOS() {
        $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
        if (strpos($agent, 'windows nt')) {
            $platform = 'windows';
        } elseif (strpos($agent, 'macintosh')) {
            $platform = 'mac';
        } elseif (strpos($agent, 'ipod')) {
            $platform = 'ipod';
        } elseif (strpos($agent, 'ipad')) {
            $platform = 'ipad';
        } elseif (strpos($agent, 'iphone')) {
            $platform = 'iphone';
        } elseif (strpos($agent, 'android')) {
            $platform = 'android';
        } elseif (strpos($agent, 'unix')) {
            $platform = 'unix';
        } elseif (strpos($agent, 'linux')) {
            $platform = 'linux';
        } else {
            $platform = 'other';
        }
        return $platform;
    }

    public function deliver() {
        $id = I('id', 0);
        if ($id <= 0) {
            $this->redirect('Carowner/index');
        }
        $my_vip = $this->user_info['vip'];
        $state = $this->user_info['state'];
        
        // 车主查看货源详情页, 如符合常跑路线, 记录 reads
        
        $read = M('reads')->where(['user_id' => $this->user_info['id'], 'good_id' => $id])->find();
        if (!$read) {
            // 货源详情
            $good = M('goods')->where('id=%d', [$id])->find();
            $where = [
                'start_province' => $good['start_province'],
                'start_city'     => $good['start_city'],
                'start_district' => $good['start_district'],
                'end_province'   => $good['end_province'],
                'end_city'       => $good['end_city'],
                'end_district'   => $good['end_district'],
                'user_id'        => $this->user_info['id']
            ];
            
            // 常跑路线
            $line = M('lines')->where($where)->find();
            if ($line) {
                M('reads')->add(['user_id' => $this->user_info['id'], 'good_id' => $id]);
            }
        }
        
        $this->assign('id', $id);
        $this->assign('state', $state);
        $this->assign('my_vip', $my_vip);
        $this->display();
    }

    public function services() {
        $this->display();
    }

}
