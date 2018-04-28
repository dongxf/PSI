<?php

namespace Mobile\Controller;

use Think\Controller;
use Home\Service\UserService;

class IndexController extends Controller {

	public function index() {
		$us = new UserService();
		
		if ($us->hasPermission()) {
			$this->assign("title", "首页");
			$this->assign("uri", __ROOT__ . "/");
			
			$dtFlag = getdate();
			$this->assign("dtFlag", $dtFlag[0]);
			
			$this->display();
		} else {
			$url = __ROOT__ . "/Mobile/User/login";
			
			redirect($url);
		}
	}
}