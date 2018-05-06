<?php

namespace H5\Controller;

use Think\Controller;
use H5\Service\UserServiceH5;

class UserController extends Controller {

	public function doLogin() {
		if (IS_POST) {
			$params = array(
					"loginName" => I("post.loginName"),
					"password" => I("post.password"),
					"isH5" => I("post.isH5")
			);
			
			$us = new UserServiceH5();
			$this->ajaxReturn($us->doLogin($params));
		}
	}

	public function doLogout() {
		if (IS_POST) {
			$us = new UserServiceH5();
			$us->clearLoginUserInSession();
			$this->ajaxReturn([
					"success" => true
			]);
		}
	}
}