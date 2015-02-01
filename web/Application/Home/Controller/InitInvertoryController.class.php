<?php

namespace Home\Controller;

use Think\Controller;
use Home\Service\InitInvertoryService;

class InitInvertoryController extends Controller {

    public function warehouseList() {
        if (IS_POST) {
            $this->ajaxReturn((new InitInvertoryService())->warehouseList());
        }
    }

    public function initInfoList() {
        if (IS_POST) {
            $params = array(
                "warehouseId" => I("post.warehouseId"),
                "page" => I("post.page"),
                "start" => I("post.start"),
                "limit" => I("post.limit")
            );
            $this->ajaxReturn((new InitInvertoryService())->initInfoList($params));
        }
    }

    public function goodsCategoryList() {
        if (IS_POST) {
            $this->ajaxReturn((new InitInvertoryService())->goodsCategoryList());
        }
    }

    public function goodsList() {
        if (IS_POST) {
            $params = array(
                "warehouseId" => I("post.warehouseId"),
                "categoryId" => I("post.categoryId"),
                "page" => I("post.page"),
                "start" => I("post.start"),
                "limit" => I("post.limit")
            );
            $this->ajaxReturn((new InitInvertoryService())->goodsList($params));
        }
    }

    public function commitInitInvertoryGoods() {
        if (IS_POST) {
            $params = array(
                "warehouseId" => I("post.warehouseId"),
                "goodsId" => I("post.goodsId"),
                "goodsCount" => I("post.goodsCount"),
                "goodsMoney" => I("post.goodsMoney")
            );
            $this->ajaxReturn((new InitInvertoryService())->commitInitInvertoryGoods($params));
        }
    }

    public function finish() {
        if (IS_POST) {
            $params = array(
                "warehouseId" => I("post.warehouseId"),
            );
            $this->ajaxReturn((new InitInvertoryService())->finish($params));
        }
    }
    
    public function cancel() {
        if (IS_POST) {
            $params = array(
                "warehouseId" => I("post.warehouseId"),
            );
            $this->ajaxReturn((new InitInvertoryService())->cancel($params));
        }
    }
}
