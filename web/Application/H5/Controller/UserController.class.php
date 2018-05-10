<?php

namespace H5\Controller;

use Think\Controller;
use H5\Service\UserServiceH5;
use H5\Service\BizlogServiceH5;

class UserController extends Controller {

	/**
	 * 登录
	 */
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

	/**
	 * 退出
	 */
	public function doLogout() {
		if (IS_POST) {
			$bs = new BizlogServiceH5();
			$bs->insertBizlog("从H5端退出");
			
			$us = new UserServiceH5();
			$us->clearLoginUserInSession();
			
			$this->ajaxReturn([
					"success" => true
			]);
		}
	}
}