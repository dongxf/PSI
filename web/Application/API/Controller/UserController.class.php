<?php

namespace API\Controller;

use Think\Controller;
use API\Service\UserAPIService;

class UserController extends Controller {

	/**
	 * 登录
	 */
	public function doLogin() {
		if (IS_POST) {
			$params = [
					"loginName" => I("post.loginName"),
					"password" => I("post.password")
			];
			
			$service = new UserAPIService();
			
			$this->ajaxReturn($service->doLogin($params));
		}
	}

	/**
	 * 退出
	 */
	public function doLogout() {
		if (IS_POST) {
			$this->ajaxReturn([
					"success" => true
			]);
		}
	}

	public function getDemoLoginInfo() {
		if (IS_POST) {
			
			$service = new UserAPIService();
			$this->ajaxReturn($service->getDemoLoginInfo());
		}
	}
}