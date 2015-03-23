<?php
namespace Home\Controller;
use Think\Controller;

use Home\Service\UserService;

class IndexController extends Controller {
    public function index(){
		$us = new UserService();
		
		$this->assign("title", "首页");
		$this->assign("uri", __ROOT__ . "/");
		
		$this->assign("loginUserName", $us->getLoignUserNameWithOrgFullName());
		$dtFlag = getdate();
		$this->assign("dtFlag", $dtFlag[0]);

		if ($us->hasPermission()) {
			$this->display();
		} else {
			redirect(__ROOT__ . "/Home/User/login");
		}
    }
}