<?php

namespace API\Controller;

use Think\Controller;

class UserController extends Controller {

	/**
	 * 登录
	 */
	public function doLogin() {
		if (IS_POST) {
			$this->ajaxReturn([
					"success" => false,
					"tokenId"=>"01",
					"msg" => "密码错误"
			]);
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
}