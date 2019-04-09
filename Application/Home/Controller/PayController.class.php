<?php

#-------------------------------------------------------------------------------
# Purpose:     支付
# Author:      xiezhongda@gmail.com
# Created:     2017-08-27
# Updated:     2017-08-27
#-------------------------------------------------------------------------------

namespace Home\Controller;

use Think\Controller;
use EasyWeChat\Payment\Order;
use EasyWeChat\Foundation\Application;

class PayController extends Controller {
    
    protected $model;
    protected $payment;

    public function __construct() {
        //
        $this->model = M('user_order');
        
        $app = new Application(C('OPTIONS'));
        $this->payment = $app->payment;
    }
    
    /**
     * 支付结果通知接口
     */
    public function notify() {
        $response = $this->payment->handleNotify(function($notify, $successful) {
            //M('wx_logs')->add(['value' => json_encode($notify)]);
            //M('wx_logs')->add(['value' => $successful]);

            // 使用通知里的 "微信支付订单号" 或者 "商户订单号" 去自己的数据库找到订单
            $order = $this->model->where('out_trade_no=%s', [$notify->out_trade_no])->find(); 
            //M('wx_logs')->add(['value' => json_encode($order)]);
            
            if (!$order || !isset($order['id'])) { // 如果订单不存在
                return 'Order not exist.'; // 告诉微信，我已经处理完了，订单没找到，别再通知我了
            }
            // 如果订单存在
            // 检查订单是否已经更新过支付状态
            if ($order['state'] == 1) { // 假设订单字段“支付时间”不为空代表已经支付
                return true; // 已经支付成功了就不再更新了
            }
            
            $order_update = ['state' => 2];
            // 用户是否支付成功
            if ($successful) {
                // 不是已经支付状态则修改为已经支付状态
                $order_update['pay_time'] = date('Y-m-d H:i:s'); // 更新支付时间为当前时间
                $order_update['state'] = 1;
                
                // 更新用户会员时间
                $update = ['vip' => 1];
                // 获取用户
                $user_info = M('users')->where("open_id = '%s'", [$order['openid']])->find();
                
                if ($user_info['vip'] == 1) {
                    $update['vip_endtime'] = date('Y-m-d', strtotime($user_info['vip_endtime'] . ' +1 year'));
                } else {
                    $update['vip_starttime'] = date('Y-m-d');
                    $update['vip_endtime'] = date('Y-m-d', strtotime('+1 year'));
                }
                
                M('users')->where('id=%d', [$user_info['id']])->save($update);
            }
            
            $this->model->where('id=%d', [$order['id']])->save($order_update); // 保存订单
            return true; // 或者错误消息
        });
        
        $response->send();
    }
    
}