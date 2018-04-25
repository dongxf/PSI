<?php

namespace Mobile\Controller;

use Think\Controller;

class IndexController extends Controller {

	public function index() {
		$this->assign("title", "首页");
		$this->assign("uri", __ROOT__ . "/");
		
		$dtFlag = getdate();
		$this->assign("dtFlag", $dtFlag[0]);
		
		$this->display();
	}
}