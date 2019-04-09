<?php

#-------------------------------------------------------------------------------
# Purpose:     首页管理
# Author:      xiezhongda@gmail.com
# Created:     2017-10-16
# Updated:     2017-10-16
#-------------------------------------------------------------------------------

namespace Admin\Controller;

use Admin\Controller\ParentController;

class IndexController extends ParentController {
    
    protected $type;
    protected $model;

    public function __construct() {
        parent::__construct();
        $this->model = M('ads');
        
        $this->type = I('type', 1);
        $this->assign('type', $this->type);
    }
    
    public function enterprise() {
        $this->lists();
    }
    
    public function shop() {
        $this->lists();
    }
    
    /**
     * 列表页
     */
    public function lists() {
        $page_size = C('pageSize');
        $p = I('p/d', 1);
        
        // where(
        if ($this->type == 2) {
            $w = ['type' => $this->type];
        } elseif ($this->type == 4) {
            $w = ['type' => $this->type];
        } else {
            $w = ['type' => ['in', [1,3]]];
        }
        $total = $this->model->where($w)->count();
        
        $pageInfo = pageCss($total, $page_size);
        $show = $pageInfo['show'];
        $limit = $pageInfo['limit'];
        
        // 获取列表数据
        $info = $this->model->where($w)->order('create_time desc')->limit($limit)->select();
        
        $max_p = ceil($total / $page_size);
        $max_p = $max_p < 1 ? 1 : $max_p;
        $p = $p > $max_p ? $max_p : $p;
        
        $this->assign('p', $p);
        $this->assign('page', $show);
        $this->assign('info', $info);
        $this->display('lists');
    }
    
    public function save() {
        $id = I('id/d');
        
        if ($id) {
            $info = $this->model->where('id=%d', [$id])->find();
        }
        
        if (IS_POST) {
            // 
            $params['type'] = $this->type;
            $params['name'] = I('post.name', '');
            $params['image'] = I('post.image', '');
            $params['contact'] = I('post.contact', '');
            $params['telephone'] = I('post.telephone', '');
            $params['mobile'] = I('post.mobile', '');
            $params['address'] = I('post.address', '');
            $params['category'] = I('post.category', '');
            
            if (!$params['name'] || ($params['type'] != 4 && !$params['image'])) {
                $this->redirect('/admin/index/save', ['type' => $params['type']], 1, '参数错误');
            }
                        
            if ($id) {
                $p = ['id' => $id, 'type' => $this->type];
                $res = $this->model->where('id=%d', [$id])->save($params);  
            } else {
                $p = ['type' => $this->type];
                $res = $this->model->add($params);  
            }
            
            if ($res !== false) {
                $this->redirect('/admin/index/lists', $p, 1, '操作成功');
            } else {
                $this->redirect('/admin/index/save', $p, 1, '操作失败');
            }
        }
        
        $this->assign('info', $info);
        $this->display();
    }
    
    /**
     * 删除广告
     */
    public function delete() {
        $id = I('id');
        $type = I('type');
        if (!$id) {
            error(400, '参数错误');
        }
        
        $ad = $this->model->where('id=%d', [$id])->find();
        if (!isset($ad['id'])) {
            error(400, '非法操作');
        }

        $res = $this->model->where('id=' . $ad['id'])->delete();
        if ($res) {
            // 记录日志
            addLog($this->admin['id'], $this->admin['username'], '首页管理', '删除广告', $id);

            success();
        } else {
            error(400, '操作失败');
        }
    }

    /**
     * 上传图片接口
     */
    public function upload() {
        if ($_FILES) {
            $config = array(
                'mimes' => array('image/jpeg', 'image/png', 'image/gif'), //允许上传的文件MiMe类型
                'maxSize' => 10483760, // 上传的文件大小限制 (0-不做限制)
                'exts' => array('jpg', 'jpeg', 'png'), // 允许上传的文件后缀
                'rootPath' => './upload/', // 保存根路径
                'savePath' => '', // 保存路径
            );

            $upload = new \Think\Upload($config);

            $filedata = $_FILES['file'];
            $info = $upload->uploadOne($filedata);
            if (!$info) {
                error(400, $upload->getError());
            } else {
                success(APP_HOME . '/upload/' . $info['savepath'] . $info['savename']);
            }
        }
    }
    
}
