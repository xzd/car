<?php

use EasyWeChat\Foundation\Application;

/**
 * 发送审核结果模板消息
 * @param type $openId
 * @return type
 */
function sendAuditMessage($openId, $type, $result, $remark) {
    // 判断用户是否关注
    $user = getWechatUserInfo($openId);
    if (!$user || !$user->subscribe) {
        return false;
    }
    
    $app = new Application(C('OPTIONS'));
    $notice = $app->notice;

    $userId = $openId;
    $templateId = 'YLAjmk8EMbVCQM5QK8X2cawETSDNQPNxZguFAIjhCOA';
    $url = '';
    $data = array(
        "first"  => "您好！您的注册申请已有结果",
        "keyword1" => $result,
        "keyword2" => '系统管理员',
        "keyword3" => date('Y-m-d H:i'),
        "remark" => $remark,
    );
    $res = $notice->uses($templateId)->withUrl($url)->andData($data)->andReceiver($userId)->send();
    return $res;
}

/**
 * 分页方法
 * @param     $count
 * @param int $pageSize
 * @return array
 */
function pageCss($count, $pageSize = 1) {

    $Page = new \Think\Page($count, $pageSize);
    $pages = ceil($count / $pageSize);
    if ($Page->firstRow > $count) {
        $Page->firstRow = ($pages - 1) * $pageSize;
    }

    $limit = $Page->firstRow . ',' . $pageSize;
    $Page->setConfig('header', '<p class="num">总共 %TOTAL_ROW% 条，每页 ' . $pageSize . ' 条</p>');

    $Page->setConfig('theme', '%UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %HEADER%');
    $show = $Page->show();
    
    return array('limit' => $limit, 'show' => $show);
}

/**
 * 新增日志
 * @param type $u_id
 * @param type $u_name
 * @param type $menu
 * @param type $operator
 * @param type $desc
 * @return type
 */
function addLog($u_id, $u_name, $menu, $operator, $desc = '') {
    $log = [
        'admin_id'   => $u_id,
        'admin_name' => $u_name,
        'menu'       => $menu,
        'action'     => $operator,
        'desc'       => $desc
    ];
    
    $res = M('logs')->add($log);
    return $res;
}