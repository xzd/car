<?php

#-------------------------------------------------------------------------------
# Purpose:     微信消息
# Author:      xiezhongda@gmail.com
# Created:     2017-08-08
# Updated:     2017-08-08
#-------------------------------------------------------------------------------

namespace Home\Controller;

use Think\Controller;

class WechatController extends Controller {
    
    private $debug;
    
    public function __construct($debug = false) {
        parent::__construct();
        
        $this->debug = $debug;
    }
    
    /**
     * 获取信息
     * @return type
     */
    public function getMsg() {
        // 获取信息报文
        $post_xml = $GLOBALS["HTTP_RAW_POST_DATA"];
        if (!$post_xml) {
            die('');
        }
        
        // 解析 xml 报文
        libxml_disable_entity_loader(true);
        $post = simplexml_load_string($post_xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        
        // 日志
        $this->_log($post);
        
        return $post;
    }
    
    /**
     * 发送文本消息
     * @param type $post
     */
    public function sendText($post, $content) {
        // 基本信息
        $from_user_name = $post->FromUserName;
        $to_user_name = $post->ToUserName;
        $time = time();
        
        // 消息模板
        $text = "<xml>
            <ToUserName><![CDATA[%s]]></ToUserName>
            <FromUserName><![CDATA[%s]]></FromUserName>
            <CreateTime>%s</CreateTime>
            <MsgType><![CDATA[%s]]></MsgType>
            <Content><![CDATA[%s]]></Content>
            <FuncFlag>0</FuncFlag>
        </xml>";
        
        $resultStr = sprintf($text, $from_user_name, $to_user_name, $time, 'text', $content);
        echo $resultStr;
    }
    
    /**
     * 调试模式下记录日志
     * @param type $info
     */
    private function _log($info) {
        if ($this->debug) {
            M('wechat_logs')->add(['content' => json_encode($info)]);
        }
    }

    /**
     * 验证消息服务
     */
    public function valid() {
        $echoStr = $_GET["echostr"];

        //valid signature , option
        if ($this->_checkSignature()) {
            echo $echoStr;
            exit;
        }
    }

    /**
     * 校验签名
     * @return boolean
     * @throws Exception
     */
    private function _checkSignature() {
        // you must define TOKEN by yourself
        if (!defined("TOKEN")) {
            throw new Exception('TOKEN is not defined!');
        }

        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        // use SORT_STRING rule
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);

        if ($tmpStr == $signature) {
            return true;
        } else {
            return false;
        }
    }

}
