<?php

namespace Home\Controller;

use Home\Common\DemoConst;
use Home\Service\UserService;

/**
 * 表单视图Controller
 *
 * @author 李静波
 *        
 */
class FormViewController extends PSIBaseController {

	/**
	 * 表单视图开发助手 - 主页面
	 */
	public function devIndex() {
		$us = new UserService();
		
		// 开发助手只允许admin访问
		if ($us->getLoginUserId() == DemoConst::ADMIN_USER_ID) {
			$this->initVar();
			
			$this->assign("title", "表单视图开发助手");
			
			$this->display();
		} else {
			redirect(__ROOT__ . "/Home");
		}
	}
}