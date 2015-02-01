<?php

namespace Home\Controller;

use Think\Controller;
use Home\Service\UserService;
use Home\Service\InvertoryService;
use Home\Common\FIdConst;

class InvertoryController extends Controller {

	public function initIndex() {
		$us = new UserService();

		$this->assign("title", "库存建账");
		$this->assign("uri", __ROOT__ . "/");

		$this->assign("loginUserName", $us->getLoginUserName());
		$dtFlag = getdate();
		$this->assign("dtFlag", $dtFlag[0]);

		if ($us->hasPermission(FIdConst::INVERTORY_INIT)) {
			$this->display();
		} else {
			redirect(__ROOT__ . "/Home/User/login");
		}
	}

	public function invertoryQuery() {
		$us = new UserService();

		$this->assign("title", "库存账查询");
		$this->assign("uri", __ROOT__ . "/");

		$this->assign("loginUserName", $us->getLoginUserName());
		$dtFlag = getdate();
		$this->assign("dtFlag", $dtFlag[0]);

		if ($us->hasPermission(FIdConst::INVERTORY_QUERY)) {
			$this->display();
		} else {
			redirect(__ROOT__ . "/Home/User/login");
		}
	}

	public function warehouseList() {
		if (IS_POST) {
			$is = new InvertoryService();
			$this->ajaxReturn($is->warehouseList());
		}
	}

	public function invertoryList() {
		if (IS_POST) {
			$params = array(
				"warehouseId" => I("post.warehouseId")
			);
			$is = new InvertoryService();
			$this->ajaxReturn($is->invertoryList($params));
		}
	}

	public function invertoryDetailList() {
		if (IS_POST) {
			$params = array(
				"warehouseId" => I("post.warehouseId"),
				"goodsId" => I("post.goodsId"),
				"dtFrom" => I("post.dtFrom"),
				"dtTo" => I("post.dtTo"),
				"page" => I("post.page"),
				"start" => I("post.start"),
				"limit" => I("post.limit")
			);
			$is = new InvertoryService();
			$this->ajaxReturn($is->invertoryDetailList($params));
		}
	}
}
