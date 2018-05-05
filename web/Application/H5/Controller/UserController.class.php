<?php

namespace H5\Controller;

use Think\Controller;

class UserController extends Controller {

	public function doLogin() {
		if (IS_POST) {
			$this->ajaxReturn([
					"success" => false,
					"msg" => "TODO"
			]);
		}
	}
}