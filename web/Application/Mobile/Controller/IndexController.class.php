<?php

namespace Mobile\Controller;

use Think\Controller;
use Home\Service\UserService;
use Home\Service\BizConfigService;

class IndexController extends Controller {

	public function index() {
		$us = new UserService();
		$this->assign("title", "首页");
		$this->assign("uri", __ROOT__ . "/");
		
		$dtFlag = getdate();
		$this->assign("dtFlag", $dtFlag[0]);
		
		$this->assign("loggedIn", $us->hasPermission() ? "1" : "0");
		
		// 产品名称
		$bcs = new BizConfigService();
		$productionName = $bcs->getProductionName();
		if ($productionName == "PSI") {
			$productionName .= " - 开源ERP";
		}
		$this->assign("productionName", $productionName);
		
		
		$this->display();
	}
}