<?php

namespace Home\Controller;

use Think\Controller;
use Home\Service\InitInventoryService;

/**
 * 库存建账Controller
 * @author 李静波
 *
 */
class InitInventoryController extends Controller {

    public function warehouseList() {
        if (IS_POST) {
			$is = new InitInventoryService();
            $this->ajaxReturn($is->warehouseList());
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
			$is = new InitInventoryService();
            $this->ajaxReturn($is->initInfoList($params));
        }
    }

    public function goodsCategoryList() {
        if (IS_POST) {
			$is = new InitInventoryService();
            $this->ajaxReturn($is->goodsCategoryList());
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
			$is = new InitInventoryService();
            $this->ajaxReturn($is->goodsList($params));
        }
    }

    public function commitInitInventoryGoods() {
        if (IS_POST) {
            $params = array(
                "warehouseId" => I("post.warehouseId"),
                "goodsId" => I("post.goodsId"),
                "goodsCount" => I("post.goodsCount"),
                "goodsMoney" => I("post.goodsMoney")
            );
			$is = new InitInventoryService();
            $this->ajaxReturn($is->commitInitInventoryGoods($params));
        }
    }

    public function finish() {
        if (IS_POST) {
            $params = array(
                "warehouseId" => I("post.warehouseId"),
            );
			$is = new InitInventoryService();
            $this->ajaxReturn($is->finish($params));
        }
    }
    
    public function cancel() {
        if (IS_POST) {
            $params = array(
                "warehouseId" => I("post.warehouseId"),
            );
			$is = new InitInventoryService();
            $this->ajaxReturn($is->cancel($params));
        }
    }
}
