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
			
			$service = new UserApiService();
			
			$this->ajaxReturn($service->doLogin($params));
		}
	}

	/**
	 * 退出
	 */
	public function doLogout() {
		if (IS_POST) {
			
			$params = [
					"tokenId" => I("post.tokenId")
			];
			$service = new UserApiService();
			
			$this->ajaxReturn($service->doLogout($params));
		}
	}

	public function getDemoLoginInfo() {
		if (IS_POST) {
			
			$service = new UserApiService();
			$this->ajaxReturn($service->getDemoLoginInfo());
		}
	}
}