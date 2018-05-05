<?php

namespace H5\Service;

use Home\Service\UserService;

/**
 * 用户Service for H5
 *
 * @author 李静波
 */
class UserServiceH5 extends UserService {

	/**
	 * 演示环境中显示在登录窗口上的提示文字
	 *
	 * @return string
	 */
	public function getDemoLoginInfoH5() {
		if ($this->isDemo()) {
			return "当前处于演示环境，请勿保存正式数据，默认的登录名和密码均为 admin";
		} else {
			return "";
		}
	}
}