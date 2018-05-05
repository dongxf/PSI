<?php

namespace H5\Controller;

use H5\Service\BizConfigServiceH5;
use H5\Service\UserServiceH5;
use Think\Controller;

class IndexController extends Controller {

	public function index() {
		$us = new UserServiceH5();
		
		$this->assign("title", "首页");
		$this->assign("uri", __ROOT__ . "/");
		
		$dtFlag = getdate();
		$this->assign("dtFlag", $dtFlag[0]);
		
		$this->assign("loggedIn", $us->hasPermission() ? "1" : "0");
		
		// 产品名称
		$bcs = new BizConfigServiceH5();
		$productionName = $bcs->getProductionName();
		if ($productionName == "PSI") {
			$productionName .= " - 开源ERP";
		}
		$this->assign("productionName", $productionName);
		
		$this->assign("demoLoginInfo", $us->getDemoLoginInfoH5());
		
		$this->display();
	}
}